<?php

require_once('Base.php');

class Attachment extends Base {
    public $properties;
    public static $validationrules = [];

    public $exposedInfo = [
        "ADMIN" => [
            "i_attachment",
            "i_mail",
            "s_filename",
            "s_name",
            "s_contenttype",
            "s_cid",
            "n_size",
            "n_index",
            "d_inserted"
        ]
    ];

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
            $this->attachment = $attachment;
            $this->populateFromObject();
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
            $this->properties["i_attachment"] = $db->insert(
                "attachment",
                $this->properties
            );
        }

        // if attachment object is given, save the file
        if (isset($this->attachment)) {
            $this->attachment->save(ATTACHMENT_PATH, $filename = $this->properties["s_filename"]);
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

    protected function populateFromObject() {
        $this->properties = [
            "s_contenttype" => $this->attachment->getAttributes()["content_type"],
            "s_cid"         => $this->attachment->getAttributes()["id"],
            "s_name"        => $this->attachment->getAttributes()["name"],
            "n_size"        => $this->attachment->getAttributes()["size"],
        ];
    }

    public function getUrl() {
        // get the url relative to the api root
        return "/attachment/" . $this->properties["i_attachment"] . "/" . $this->properties["s_name"];
    }

    public function apiGetAddInfo($role, $properties, $fields) {
        $properties["url"] = $this->getUrl();
        return $properties;
    }
}