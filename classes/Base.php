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

    public static function getAll() {
        // get all objects of this type from the DB and return as list
        $db = new DB();
        $ids = $db->queryColumn("SELECT " . static::$identifier . " FROM " . static::$table);
        return static::getObjects($ids);
    }

    public static function getObjects($ids) {
        // get an array of objects based on an array of ids
        $objects = [];
        foreach ($ids as $id) {
            $objects[] = new static($id = $id);
        }

        return $objects;
    }

    /**
     * Return objects which match one or more filter criteria
     * @param array|string $filter either array of filter rules with key as column name and value as value or SQL WHERE clause
     * @return static[] array of objects which match the filter conditions
     */
    public static function getObjectsFilter(array $filter) {
        $db = new DB();
        if (!is_string($filter)) {
            $filterString = $db->buildWhere($filter, "AND");
        } else {
            $filterString = $filter;
        }
        $ids = $db->queryColumn("SELECT " . static::$identifier . " FROM " . static::$table . " WHERE " . $filterString, $filter);
        return static::getObjects($ids);
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