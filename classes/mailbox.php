<?php

class Mailbox {
    public $properties;
    public static $validationrules = [
        "s_address"     => '/^[^\s@]+@[^\s@]+$/',
        "s_imapserver"  => '/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/',
        "n_imapport"    => '/[0-9]*/'
    ];

    public function __construct($i_mailbox, $properties = NULL) {
        if (is_null($i_mailbox) && is_null($properties)) {
            throw new Exception('Mailbox must be instantiated with at least one identifying paramter');
        }
        $this->load($i_mailbox, $properties);
    }

    public function load($i_mailbox, $properties) {
        // load data from DB or parameters
        $db = new DB();
        if (!is_null($i_mailbox)) {
            $this->properties = $db->queryRow("SELECT * FROM mailbox WHERE i_mailbox = ?", [$i_mailbox]);
        } else {
            // load data from properties
            $this->properties = $properties;
        }
    }

    public function save() {
        // save data to DB
        $db = new DB();

        // check if properties match validation rules
        if (!$this->validate()) {
            return false;
        }

        // if i_mailbox is set, update record, otherwise create new
        if (isset($this->properties["i_mailbox"])) {
            $db->update(
                "mailbox",
                $this->properties,
                ["i_mailbox" => $this->properties["i_mailbox"]]
            );
        } else {
            $db->insert(
                "mailbox",
                $this->properties
            );
        }
    }

    public function validate() {
        foreach (self::$validationrules as $key => $rule) {
            if (!preg_match($rule, $this->properties[$key])) {
                trigger_error("Property $key not matching validation rule");
                return false;
            }
        }
        return true;
    }

    public function fetch_mails() {
        // fetch emails from imap mailbox
    }

    public function get_recipients() {
        // get the recipients of a mailinglist
    }


}