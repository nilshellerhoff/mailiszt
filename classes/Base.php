<?php

class Base {
    // This is the base class, which offers some abstraction for loading and saving data
    // this will be extended by subclasses and never instantiated on its own

    public $properties;
    public static $validationrules = [];

    // list, which informations are exposed over the api
    // right now there are two roles: READER and ADMIN
    public $exposedInfo = [
        "READER"    => [],
        "ADMIN"     => []
    ];
    
    public static $table = ""; // table name where class data is stored
    public static $identifier = ""; // identifier in $table

    /**
     * Get all objects of this class from the database
     * 
     * @param int $limit limit the number of objects returned
     * @param int $offset offset of the returned objects (to be used in combination with limit)
     * 
     * @return static[] array of objects
     */
    public static function getAll(int $limit = 0, int $offset = 0) {
        // get all objects of this type from the DB and return as list
        $db = new DB();


        if ($limit) {
            $limitString = $limit >= 0 ? " LIMIT " . $limit : "";
            $offsetString = $offset >= 0 ? " OFFSET " . $offset : "";
            $query = "SELECT " . static::$identifier . " FROM " . static::$table . $limitString . $offsetString;
        } else {
            $query ="SELECT * FROM " . static::$table;
        }

        $ids = $db->queryColumn($query);
        return static::getObjects($ids);
    }

    /**
     * Get the objects with the given ids from the database
     * 
     * @return static[] array of objects
     */
    public static function getObjects($ids, int $limit = 0, int $offset = 0) {
        // get an array of objects based on an array of ids
        $objects = [];
        foreach ($ids as $id) {
            $objects[] = new static($id = $id);
        }

        return $objects;
    }

    /**
     * Return objects which match one or more filter criteria
     * 
     * @param array $filter associative array of filter rules as column => value
     * @param int $limit limit the number of objects returned
     * @param int $offset offset of the returned objects (to be used in combination with limit)
     * 
     * @return static[] array of objects which match the filter conditions
     */
    public static function getObjectsFilter(array $filter, int $limit = -1, int $offset = -1) {
        $db = new DB();
        if ($filter) {
            $filterString = $db->buildWhere($filter, "AND");

            if ($limit) {
                $limitString = $limit >= 0 ? " LIMIT " . $limit : "";
                $offsetString = $offset >= 0 ? " OFFSET " . $offset : "";
                $query = "SELECT " . static::$identifier . " FROM " . static::$table . " WHERE " . $filterString . $limitString . $offsetString;
            } else {
                $query = "SELECT " . static::$identifier . " FROM " . static::$table . " WHERE " . $filterString;
            }
    
            $ids = $db->queryColumn($query, $filter);
            return static::getObjects($ids);    
        } else {
            return static::getAll($limit, $offset);
        }
    }

    /**
     * Get the count of the objects of this type in the DB
     * nils
     * @return int number of objects of this class in the DB
     */
    public static function getObjectsCount() {
        $db = new DB();
        $query = "SELECT count(*) FROM " . static::$table;
        return $db->queryScalar($query);
    }

    public function __construct($id, $obj = NULL, $prop = NULL) {
        if (is_null($id) && is_null($obj) && is_null($prop)) {
            throw new Exception(get_class($this) . ' must be instantiated with data');
        }
        $this->load($id, $obj, $prop);
    }

    private function load($id, $obj, $prop) {
        // load data from DB, passed object or properties
        $db = new DB();
        if (!is_null($id)) {
            $this->properties = $db->queryRow("SELECT * FROM " . static::$table . " WHERE " . static::$identifier . " = ?", [$id]);
        } else if (!is_null($obj)) {
            $this->object = $obj;
            $this->populateFromObject(); // this method will be class specific
        } else {
            $this->properties = $prop;
        }
    }

    protected function populateFromObject() {}

    public function updateProperties($properties) {
        // update the member properties without overwriting the properties array
        foreach ($properties as $key => $value) {
            $this->properties[$key] = $value;
        }
    }

    function save() {
        // save data to DB
        $db = new DB();

        // check if properties match validation rules
        if (!$this->validate()) {
            return false;
        }

        // if dontSave is set, dont save :)
        if (isset($this->dontSave) && $this->dontSave) {
            return;
        }

        // if identifier is set, update record, otherwise create new
        if (isset($this->properties[static::$identifier])) {
            $db->update(
                static::$table,
                $this->properties,
                [static::$identifier => $this->properties[static::$identifier]]
            );
        } else {
            $this->properties[static::$identifier] = $db->insert(
                static::$table,
                $this->properties
            );
        }

        // provide hook for action after main save event
        $this->afterSave();
    }

    public function afterSave() {}

    public function delete() {
        // delete the entry from DB
        $db = new DB();
        $db->delete(
            static::$table,
            [static::$identifier => $this->properties[static::$identifier]]
        );

        $this->afterDelete();
    }

    public function afterDelete() {}

    function validate() {
        // validate the object properties
        foreach (self::$validationrules as $key => $rule) {
            if (!preg_match($rule, $this->properties[$key])) {
                trigger_error("Property $key not matching validation rule");
                return false;
            }
        }
        return true;
    }

    public function apiGetInfo($role, $fields = null) {
        // get the info appropriate to the user role
        // $fields is a list of fields which should be returned. If empty, all will be returned

        $desired_fields = $fields ? $fields : $this->exposedInfo[$role];
        // fields actually returned are desired fields, which are exposed
        $return_fields = array_intersect($desired_fields, $this->exposedInfo[$role]);

        $properties = array_intersect_key(
            $this->properties,
            array_flip($return_fields)
        );
        return $this->apiGetAddInfo($role, $properties, $fields);
    }

    public function apiGetAddInfo($role, $properties, $fields) {
        // empty function, which can be overwritten by subclasses
        // this can be used to add additional information to the api response and is called by apiGetInfo()
        return $properties;
    }
}