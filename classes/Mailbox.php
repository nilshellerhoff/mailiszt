<?php

require_once('Base.php');

class Mailbox extends Base {
    public static $table = "mailbox";
    public static $identifier = "i_mailbox";

    public $exposedInfo = [
        "ADMIN" => [
            "i_mailbox",
            "s_name",
            "s_address",
            "s_imapserver",
            "n_imapport",
            "s_smtpserver",
            "n_smtpport",
            "s_username",
            "s_groupsmethod",
            "j_groups",
            "j_groupslogic",
            "s_replyto",
            "b_overridereplyto",
            "s_allowedsenders",
            "j_allowedsenderspeople",
            "d_inserted", 
            "d_updated",
            "b_sendwelcometext",
            "s_welcometext"
        ],
    ];

    public $properties;
    public static $validationrules = [
        "s_address"     => '/^[^\s@]+@[^\s@]+$/',
        "s_imapserver"  => '/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/',
        "n_imapport"    => '/[0-9]*/',
        "s_smtpserver"  => '/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/',
        "n_smtpport"    => '/[0-9]*/'
    ];

    public function fetchMails() {
        // connect to the mailbox
        $cm = new Webklex\PHPIMAP\ClientManager($config = [
            'options' => [
                'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
                'rfc822' => false
            ]
        ]);
        $client = $cm->make([
            'host'          => $this->properties["s_imapserver"],
            'port'          => $this->properties["n_imapport"],
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => $this->properties["s_username"],
            'password'      => $this->properties["s_password"],
            'protocol'      => 'imap'
        ]);
        $client->connect();

        // create the folder for processed mail if necessary
        if (MAIL_PROCESSED_ACTION == "move") {
            $client->createFolder($folder = MAIL_PROCESSED_FOLDER, $expunge = true);
        }

        // open INBOX and return all mails
        $folder = $client->getFolder(IMAP_PENDING_FOLDER);
        
        $messages = $folder->messages()->all()->get();

        $mails = [];
        foreach ($messages as $message) {
            $mails[] = new Mail($i_mail = NULL, $message = $message);
            // set i_mailbox parameter
            $mails[count($mails) - 1]->properties["i_mailbox"] = $this->properties["i_mailbox"];
        }

        return $mails;
    }

    public function getRecipients() {
        // get the recipient list for this address
        if ($this->properties["s_groupsmethod"] == 'logic') {
            return Mailbox::getRecipientsCondition(
                json_decode($this->properties["j_groupslogic"], true)
            );
        } else {
            return Mailbox::getRecipientsSimple(
                json_decode($this->properties["j_groups"])
            );
        }
    }

    public static function getRecipientsSimple($groups) {
        // get the recipients for the mailbox based on a simple list of group IDs
        // get a list of group IDs, make sure we are dealing with ints
        $groups_list = implode(',', array_map('intval', $groups));
        $sql_query = <<<SQL
SELECT DISTINCT m.* FROM member m
INNER JOIN member2group mg ON mg.i_member = m.i_member
WHERE mg.i_group IN ($groups_list) AND m.b_active = 1
SQL;

        $db = new DB();
        return $db->queryAll($sql_query);
    }

    public static function getRecipientsCondition($condition) {
        // static function for getting the recipients when supplying a custom condition
        $db = new DB();
        $whereCond = Mailbox::getSqlExpression($condition);
        $sql_query = "SELECT DISTINCT m.* FROM member m WHERE $whereCond AND m.b_active = 1";
        return $db->queryAll($sql_query);
    }

    public function getGroups() {
        $groupsjson = $this->properties["s_groupsjson"];
        return json_decode($groupsjson);
    }

    public function setGroups($groups) {
        // set the groups condition for this mailbox (JSON and SQL)
        $this->properties["j_groupslogic"] = json_encode($groups);
    }

    private static function getSqlExpression($condition) {
        // get the sql expression from a logical conditional tree
        $sql = '';

        // allowed operators, entities and values (to prevent injection attacks)
        $allowed_logic_operators = '/AND|OR/';
        $allowed_comparison_operators = '/=|<>/';
        $allowed_entities = '/i_group/';
        $allowed_values = '/[0-9]*/';

        if (count($condition["arguments"]) == 0) {
            // we have a direct entity-value condition
            // add a direct expression (i.e. column=value or column<>value)
            if (!preg_match($allowed_comparison_operators, $condition["comparisonOperator"])) {
                die("comparison operator not allowed");
            }
            if (!preg_match($allowed_entities, $condition["entity"])) {
                die("entity not allowed");
            }
            if (!preg_match($allowed_values, $condition["value"])) {
                die("value not allowed");
            }

            if ($condition['comparisonOperator'] == '<>') {
                $sql .= "NOT EXISTS ( SELECT 1 FROM member2group WHERE i_member = m.i_member AND {$condition['entity']} = {$condition['value']} )";
            } else {
                $sql .= "EXISTS ( SELECT 1 FROM member2group WHERE i_member = m.i_member AND {$condition['entity']} = {$condition['value']} )";
            }

        } else {
            // we have a subcondition
            // recursively call this method on each of the subconditions and put parentheses around it
            for ($i = 0; $i < count($condition["arguments"]); $i++) {
                $sql .=
                "(" . Mailbox::getSqlExpression($condition["arguments"][$i]) . ")";

                // if we are not in the last operation add the operator (so it stands between the arguments)
                if ($i != count($condition["arguments"]) - 1) {
                    if (!preg_match($allowed_logic_operators, $condition["logicOperator"])) {
                        die("logic operator not allowed");
                    }
                    $sql .= " " . $condition["logicOperator"] . " ";
                }
            }
        }

        return $sql;
    }

    public function getNewRecipients($old_recipients) {
        // return an array with new recipients compared to the old recpients
        $recipients = $this->getRecipients();
        return array_diff($recipients, $old_recipients);
    }
}