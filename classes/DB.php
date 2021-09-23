<?php

class DB extends SQLite3 {
    function __construct() {
        if (file_exists(DB_FILE)) {
            $this->open(DB_FILE);
        } else {
            // if DB file doesn't exist, create DB, make schema and insert sampledata
            $this->open(DB_FILE);
            $this->executeFile(BASE_DIR . 'tables.sql');
            $this->executeFile(BASE_DIR .'sampledata.sql');
        }
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

    public function queryRow($query, $parameters = []) {
        // execute query and return one row
        $stmt = $this->prepare($query);
        for ($i = 0; $i < count($parameters); $i++) {
            $stmt->bindValue($i+1, $parameters[$i]);
        }

        // filter numeric columns
        $results = array_filter($stmt->execute()->fetchArray(), function(&$key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        
        return $results;
    }

    public function queryColumn($query, $parameters = []) {
        // execute query and return one column
        $values = $this->queryAll($query, $parameters);
        $key_first = array_keys($values[0])[0];
        return array_column($values, $key_first);
    }

    public function queryAll($query, $parameters = []) {
        // execute query and return all rows
        $stmt = $this->prepare($query);
        for ($i = 0; $i < count($parameters); $i++) {
            $stmt->bindValue($i+1, $parameters[$i]);
        }
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
        $stmt = $this->prepare($query);
        for ($i = 0; $i < count($parameters); $i++) {
            $stmt->bindValue($i+1, $parameters[$i]);
        }

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