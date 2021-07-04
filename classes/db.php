<?php

class DB extends SQLite3 {
    function __construct() {
        if (file_exists(DB_FILE)) {
            $this->open(DB_FILE);
        } else {
            // if DB file didnt exist, create DB, make schema and insert sampledata
            $this->open(DB_FILE);
            $this->executeFile('tables.sql');
            $this->executeFile('sampledata.sql');
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

    function insert($table, $values) {
        // insert one row into a table
        // columns as comma separated string
        $insert_cols = implode(', ', array_keys($values));

        // values (column names) prefixed with :
        $insert_vals = array_keys($values);
        array_walk($insert_vals, function(&$item) {
            $item = ":" . $item;
        });
        $insert_vals = implode(', ', $insert_vals);

        $stmt = $this->prepare("INSERT INTO $table ($insert_cols) VALUES ($insert_vals)");

        // bind values 
        foreach ($values as $key=>$value) {
            $stmt->bindValue(":" . $key, $value);
        }

        $stmt->execute();

        return $this->lastInsertRowID();
    }
}