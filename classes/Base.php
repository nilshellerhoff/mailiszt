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
    
    public $table = ""; // table name where class data is stored
    public $identifier = ""; // identifier in $table

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
            $this->properties = $db->queryRow("SELECT * FROM $this->table WHERE $this->identifier = ?", [$id]);
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
        if (isset($this->properties[$this->identifier])) {
            $db->update(
                $this->table,
                $this->properties,
                [$this->identifier => $this->properties[$this->identifier]]
            );
        } else {
            $this->properties[$this->identifier] = $db->insert(
                $this->table,
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
            $this->table,
            [$this->identifier => $this->properties[$this->identifier]]
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
        return $this->apiGetAddInfo($properties, $fields);
    }

    public function apiGetAddInfo($properties, $fields) {
        // empty function, which can be overwritten by subclasses
        // this can be used to add additional information to the api response and is called by apiGetInfo()
        return $properties;
    }
}