<?php

class DB extends SQLite3 {
    function __construct() {
        if (file_exists(DB_FILE)) {
            try {
                $this->open(DB_FILE);
            } catch (Exception $e) {
                Logger::error("Could not open DB file: " . $e->getMessage(), 'DB_OPEN_FAILED');
            }
        } else {
            // if DB file doesn't exist, create DB, make schema and insert sampledata
            try {
                $this->open(DB_FILE);
                $this->executeFile(BASE_DIR . 'sql/tables.sql');
                $this->executeFile(BASE_DIR . 'sql/basedata.sql');
                $this->executeFile(BASE_DIR .'sql/sampledata.sql');
                $this->upgradeDB();
            } catch (Exception $e) {
                Logger::error("Could not create DB file: " . $e->getMessage(), 'DB_CREATE_FAILED');
            }
        }
    }

    function upgradeDB() {
        // check if the DB is the newest available version under the sql/migrations directory
        // if not apply migrations in order
        $available_migrations = $this->getMigrations();
        foreach ($available_migrations as $migration) {
            $db_version = $this->getDBVersion();
            if (version_compare($migration, $db_version) > 0) {
                try {
                    Logger::info("migrating DB from {$db_version} to {$migration}", 'DB_MIGRATION_START');
                    $this->executeFile(BASE_DIR . 'sql/migrations/' . $migration . '.sql');
                    $this->insert('db_upgrade', [
                        "s_version"         => $migration,
                        "s_version_from"    => $db_version,
                    ]);
                    Setting::setValue('version_number', $migration);
                    Logger::info("migrated DB from {$db_version} to {$migration}", 'DB_MIGRATION_DONE');
                } catch (\Throwable $e) {
                    Logger::error("could not migrate from {$db_version} to {$migration}. Error: {$e->getMessage()}", 'DB_MIGRATION_FAILED');
                }
            }
        }
    }

    function getMigrations() {
        // get an array with the available migrations
        $migration_files = scandir(BASE_DIR . 'sql/migrations/');
        $migrations = [];
        foreach ($migration_files as $file) {
            if ($file != '.' && $file != '..') {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        usort($migrations, 'version_compare');
        return $migrations;
        
    }

    function getDBVersion() {
        // get the current DB version
        try {
            $db_version = $this->queryScalar("SELECT s_version FROM db_upgrade ORDER BY s_version DESC LIMIT 1");
        } catch (\Throwable $e) {
            $db_version = '0';
        }
        return $db_version;
    }

    function executeFile($filename) {
        // execute a sql query from file if exists
        if (file_exists($filename)) {
            $sql = file_get_contents($filename);
            return $this->exec($sql);
        }
        return -1;
    }

    function fetchAll($result) {
        $rows = [];
        while ($row = $result->fetchArray()) {
            $rows[] = array_filter($row, function(&$key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $rows;
    }

    public function execute($query, $parameters = []) {
        // execute query and return one row
        $stmt = $this->prepare($query);
        for ($i = 0; $i < count($parameters); $i++) {
            $stmt->bindValue($i+1, $parameters[$i]);
        }

        $stmt->execute();

        return;
    }

    /**
     * Build a query using parameters given
     * 
     * @param $query SQL query with parameters as :param
     * @param $parameters either array of values to be replaced or associative array of parameters with column_name => value
     * 
     * @return SQLite3Stmt SQL query with parameters replaced
     */
    public function getQuery(string $query, array $parameters = []) {
        $stmt = $this->prepare($query);

        // if parameters is an associative array, replace the :param with the value
        if (count(array_filter(array_keys($parameters), 'is_string')) > 0) {
            foreach ($parameters as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        } else {
            // if parameters is an array, go by index
            for ($i = 0; $i < count($parameters); $i++) {
                $stmt->bindValue($i+1, $parameters[$i]);
            }
        }

        return $stmt;
    }

    /**
     * Build a where clause from a flat array of conditions. 
     * 
     * @param $wheres where keys are column names and values are values
     * @param $operator string the operator to use for the where clause (either 'AND' or 'OR') defaults to 'AND'
     * 
     * @return string SQL where clause
     */
    public function buildWhere(array $wheres, string $operator = 'AND') {
        $whereClause = "";
        foreach ($wheres as $column => $value) {
            if ($whereClause != "") {
                $whereClause .= " $operator ";
            }
            $column_escaped = SQlite3::escapeString($column);
            $whereClause .= $column_escaped . " = :" . $column_escaped;
        }
        return $whereClause;        
    }

    public function queryRow($query, $parameters = []) {
        $stmt = $this->getQuery($query, $parameters);

        // filter numeric columns
        $results = array_filter($stmt->execute()->fetchArray(), function(&$key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        return $results;
    }

    public function queryColumn($query, $parameters = []) {
        // execute query and return one column
        $values = $this->queryAll($query, $parameters);
        if (count($values) > 0) {
            return array_column($values, key($values[0]));
        } else {
            return [];
        }
    }

    public function queryAll($query, $parameters = []) {
        $stmt = $this->getQuery($query, $parameters);
        $result = $stmt->execute();

        // loop through rows and return only with non-numeric index (otherwise double columns)
        $rows = [];
        while ($row = $result->fetchArray()) {
            $rows[] = array_filter($row, function(&$key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $rows;
    }

    public function queryScalar($query, $parameters = []) {
        // execute query and return a scalar (first row, first column)
        $stmt = $this->getQuery($query, $parameters);

        $result = $stmt->execute()->fetchArray();
        if ($result) {
            return $result[0];
        }
        return $result;
    }

    public function insert($table, $values) {
        // insert one row into a table
        // columns as comma separated string
        $insert_cols = implode(', ', array_keys($values));

        // values (column names) prefixed with :
        $insert_vals = array_keys($values);
        array_walk($insert_vals, function(&$item) {
            $item = ":" . $item;
        });
        $insert_vals = implode(', ', $insert_vals);

        $stmt = $this->prepare("INSERT INTO $table ($insert_cols, d_inserted) VALUES ($insert_vals, DATETIME('now'))");

        // bind values 
        foreach ($values as $key=>$value) {
            $stmt->bindValue(":" . $key, $value);
        }

        $stmt->execute();

        return $this->lastInsertRowID();
    }

    public function update($table, $values, $wheres) {
        // update a row in table
        $update_cols = [];
        foreach ($values as $key=>$value) {
            $update_cols[] = $key . " = :" . $key;
        }
        $update_cols = implode(', ', $update_cols);

        $where_cols = [];
        foreach ($wheres as $key=>$value) {
            $where_cols[] = $key . " = :" . $key;
        }
        $where_cols = implode(', ', $where_cols);

        $stmt = $this->prepare("UPDATE $table SET $update_cols WHERE $where_cols");

        // bind values 
        foreach ($values as $key=>$value) {
            $stmt->bindValue(":" . $key, $value);
        }
        // bind wheres
        foreach ($wheres as $key=>$value) {
            $stmt->bindValue(":" . $key, $value);
        }
        
        $stmt->execute();
        return $this->lastInsertRowID();

    }

    public function delete($table, $wheres) {
        // delete entries from a table where conditions apply
        $where_cols = [];
        foreach ($wheres as $key=>$value) {
            $where_cols[] = $key . " = :" . $key;
        }
        $where_cols = implode(', ', $where_cols);

        $stmt = $this->prepare("DELETE FROM $table WHERE $where_cols");

        // bind wheres
        foreach ($wheres as $key=>$value) {
            $stmt->bindValue(":" . $key, $value);
        }
        
        $stmt->execute();
    }
}