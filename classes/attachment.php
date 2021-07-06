<?php

class Attachment {
    public $properties;
    public static $validationrules = [];

    function __construct($i_attachment, $attachment = NULL, $properties = NULL) {
        if (is_null($i_attachment) && is_null($attachment) && is_null($properties)) {
            throw new Exception('Attachment must be instantiated with data');
        }
        $this->load($i_attachment, $attachment, $properties);
    }

    private function load($i_attachment, $attachment, $properties) {
        // load data from DB or parameters
        $db = new DB();
        if (!is_null($i_attachment)) {
            $this->properties = $db->queryRow("SELECT * FROM attachment WHERE i_attachment = ?", [$i_attachment]);
        } else if (!is_null($attachment)) {
            $this->populateFromObject($attachment);
        } else {
            // load data from properties
            $this->properties = $properties;
        }
    }

    function save() {
        // save data to DB
        $db = new DB();

        // check if properties match validation rules
        if (!$this->validate()) {
            return false;
        }

        // if i_attachment is set, update record, otherwise create new
        if (isset($this->properties["i_attachment"])) {
            $db->update(
                "attachment",
                $this->properties,
                ["i_attachment" => $this->properties["i_attachment"]]
            );
        } else {
            $db->insert(
                "attachment",
                $this->properties
            );
        }
    }

    function validate() {
        foreach (self::$validationrules as $key => $rule) {
            if (!preg_match($rule, $this->properties[$key])) {
                trigger_error("Property $key not matching validation rule");
                return false;
            }
        }
        return true;
    }

    private function populateFromObject($attachment) {
        $this->properties = [
            "s_contenttype" => $attachment->getAttributes()["content_type"],
            "s_cid"         => $attachment->getAttributes()["id"],
            "s_name"        => $attachment->getAttributes()["name"],
            "n_size"        => $attachment->getAttributes()["size"],
        ];
    }

    function saveAttachment($attachment) {
        $attachment->save('data/attachments/', $filename = $this->properties["s_filename"]);
    }
}