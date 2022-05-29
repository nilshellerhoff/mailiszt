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
            "s_replytooverride",
            "s_allowedsenders",
            "j_allowedsenderspeople",
            "d_inserted", 
            "d_updated",
            "b_includeinactive",
        ],
    ];

    public $properties;
    public static $validationrules = [
        "s_address"     => [
            'required'  => true,
            'regex'     => '^[^\s@]+@[^\s@]+$',
            'message'   => 'Please insert a valid email address',
        ],
        "s_imapserver"  => [
            'required'  => true,
            'regex'     => '^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$',
            'message'   => 'Please insert a valid domain name',
        ],
        "n_imapport"    => [
            'required'  => true,
            'regex'     => '^[0-9]*$',
            'message'   => 'Please insert a valid port number',
        ],
        "s_smtpserver"  => [
            'required'  => true,
            'regex'     => '^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$',
            'message'   => 'Please insert a valid domain name',
        ],
        "n_smtpport"    => [
            'required'  => true,
            'regex'     => '^[0-9]*$',
            'message'   => 'Please insert a valid port number',
        ],
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
            return $this->getRecipientsCondition();
        } else {
            return $this->getRecipientsSimple();
        }
    }

    public function getRecipientsSimple() {
        // get the recipients for the mailbox based on a simple list of group IDs
        // get a list of group IDs, make sure we are dealing with ints
        $groups = json_decode($this->properties["j_groups"], true);
        $groups_list = implode(',', array_map('intval', $groups));

        // based on whether to include inactive members have an additional condition
        $active_condition = $this->properties["b_includeinactive"] ? '' : 'AND m.b_active = 1';
        $sql_query = <<<SQL
SELECT DISTINCT m.* FROM member m
INNER JOIN member2group mg ON mg.i_member = m.i_member
WHERE mg.i_group IN ($groups_list)
$active_condition
SQL;

        $db = new DB();
        return $db->queryAll($sql_query);
    }

    public function getRecipientsCondition() {
        // function for getting the recipients when supplying a custom condition
        $condition = json_decode($this->properties["j_groupslogic"], true);

        // based on whether to include inactive members have an additional condition
        $active_condition = $this->properties["b_includeinactive"] ? '' : 'AND m.b_active = 1';

        $whereCond = Mailbox::getSqlExpression($condition);
        $sql_query = "SELECT DISTINCT m.* FROM member m WHERE $whereCond $active_condition";

        $db = new DB();
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

    public function getReplyToAddressesNoReplyTo($sender_mail, $sender_name) {
        // return the reply-to address in the case that no reply-to header is given

        $sender_address = ["address" => $sender_mail, "name" => $sender_name];
        $list_address = ["address" => $this->properties["s_address"], "name" => $this->properties["s_name"]];

        switch ($this->properties["s_replyto"]) {
            case "sender":
                $replyTos = [$sender_address];
                break;
            case "mailinglist":
                $replyTos = [$list_address];
                break;
            case "sender+mailinglist":
                $replyTos = [$sender_address, $list_address];
                break;
            default:
                $replyTos = [$sender_address, $list_address];
                break;
            }

        return $replyTos;
    }

    public function getReplyToAddresses($from_mail, $from_name, $replyto_mail, $replyto_name) {
        // get an array with the reply to addresses and names based on the mailbox setting

        // put the relevant addresses in an array so we dont need to handle that later
        $replyto_address = ["address" => $replyto_mail, "name" => $replyto_name];

        // check if a reply to address is given
        $reply_to_is_set = ( trim($replyto_mail) ?? '' ) != '';

        if ( $reply_to_is_set ) {
            // if a reply to address is given, base on s_replytooverride setting
            switch ($this->properties["s_replytooverride"]) {
                case "default":
                    // use the default reply-to address
                    $replyTos = $this->getReplyToAddressesNoReplyTo($from_mail, $from_name);
                    break;
                case "add":
                    // use the default reply-to address and add the given reply-to-header
                    $replyTos = $this->getReplyToAddressesNoReplyTo($from_mail, $from_name);
                    $replyTos[] = $replyto_address;
                    break;
                case "replacesender":
                    // use the default reply-to address but with the given address as sender
                    $replyTos = $this->getReplyToAddressesNoReplyTo($replyto_mail, $replyto_name);
                    break;
                case "replace":
                    // only use the given reply-to address
                    $replyTos = [$replyto_address];
                    break;
            }
        } else {
            // if not just use the default
            $replyTos = $this->getReplyToAddressesNoReplyTo($from_mail, $from_name);
        }

        // now we check for duplicates in the array
        foreach ( $replyTos as $index1 => $value1 ) {
            foreach ( $replyTos as $index2 => $value2 ) {
                // we check if the address is present somewhere later in the array
                if ( $index1 < $index2 && $value1["address"] == $value2["address"] ) {
                    unset( $replyTos[$index2]);
                }
            }
        }
        
        // now we check whether any of the addresses are in the list already
        // if yes we remove them, otherwise people would get emails twice
        $recipients_emails = array_map(fn($x) => $x["s_email"], $this->getRecipients());
        foreach ( $replyTos as $index => $value ) {
            foreach ( $recipients_emails as $recaddress ) {
                if ( $value["address"] == $recaddress ) {
                    unset( $replyTos[$index]);
                }
            }
        }
        
        return $replyTos;        
    }
}