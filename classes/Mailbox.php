<?php

require_once('Base.php');

// php-imap
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

class Mailbox extends Base {
    public $table = "mailbox";
    public $identifier = "i_mailbox";

    public $exposedInfo = [
        "READER"    => ["i_mailbox", "s_name", "s_address", "s_imapserver", "n_imapport", "s_smtpserver", "n_smtpport", "s_username", "s_groupselectionsql", "d_inserted", "d_updated"],
        "ADMIN"     => ["i_mailbox", "s_name", "s_address", "s_imapserver", "n_imapport", "s_smtpserver", "n_smtpport", "s_username", "s_groupselectionsql", "d_inserted", "d_updated"],
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
        $cm = new ClientManager('php_imap_config.php');
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
        $db = new DB();
        $whereCond = $this->properties["s_groupssql"];
        $sql_query = "
SELECT m.* FROM member m
INNER JOIN member2group mg ON mg.i_member = m.i_member
WHERE $whereCond
        ";
        return $db->queryAll($sql_query);
    }

    public function getGroups() {
        $groupsjson = $this->properties["s_groupsjson"];
        return json_decode($groupsjson);
    }

    public function setGroups($groups) {
        // set the groups condition for this mailbox (JSON and SQL)
        $this->properties["s_groupsjson"] = json_encode($groups);

        $sql = $this->getSqlExpression($groups);
        $this->properties["s_groupssql"] = $sql;
    }

    private function getSqlExpression($condition) {
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

            $sql .= $condition["entity"] . $condition["comparisonOperator"] . $condition["value"];

        } else {
            // we have a subcondition
            // recursively call this method on each of the subconditions and put parentheses around it
            for ($i = 0; $i < count($condition["arguments"]); $i++) {
                $sql .=
                "(" . $this->getSqlExpression($condition["arguments"][$i]) . ")";

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
}