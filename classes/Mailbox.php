<?php

require_once('Base.php');

// php-mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
            "s_imapencryption",
            "s_smtpencryption",
            "i_moderator",
            "s_templatesubject",
            "s_templatefrom",
            "s_templatebody",
        ]
    ];

    public $properties;
    public static $validationrules = [
        "s_address"     => '/^[^\s@]+@[^\s@]+$/',
        "s_imapserver"  => '/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/',
        "n_imapport"    => '/[0-9]*/',
        "s_smtpserver"  => '/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/',
        "n_smtpport"    => '/[0-9]*/',
        "s_imapencryption" => '/^(none|ssl|tls)$/',
        "s_smtpencryption" => '/^(none|ssl|tls)$/',
    ];

    public function getSmtpEncryption() {
        // get the correct smtp encryption keyword for php-mailer
        switch ($this->properties['s_smtpencryption']) {
            case 'none':
                return '';
            case 'ssl':
                return 'ssl';
            case 'tls':
                return 'tls';
            default:
                Logger::error("s_smtpencryption $this->properties['s_smtpencryption'] is not a valid value");
        }
    }

    public function getImapEncryption() {
        // get the correct imap encryption keyword for php-imap
        switch ($this->properties['s_imapencryption']) {
            case 'none':
                return false;
            case 'ssl':
                return 'ssl';
            case 'tls':
                return 'tls';
            default:
                Logger::error("s_imapencryption $this->properties['s_imapencryption'] is not a valid value");
        }
    }

    /** Send a mail from this mailbox
     * @param string $tomail recipient email
     * @param string $toname recipient name
     * @param string $subject The subject
     * @param string $body The body
     */
    public function sendMail(string $tomail, string $toname, string $subject, string $body) {
        $mail = new PHPMailer();

        try {
            $mail->isSMTP();                                        
            $mail->Host       = $this->properties["s_smtpserver"];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->properties["s_username"];
            $mail->Password   = $this->properties["s_password"];
            $mail->SMTPSecure = $this->getSmtpEncryption();
            $mail->Port       = $this->properties["n_smtpport"];

            // set the X-Mailer header field
            $mail->XMailer = X_MAILER_HEADER;

            // allow the mail body to be empty
            $mail->AllowEmpty = true;

            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($this->properties["s_address"], $this->properties["s_name"]);

            $mail->Subject = $subject;
            // for now we only support text bodies here
            $mail->Body = $body ?? '';

            try {
                $mail->addAddress($tomail, $toname);
                $mail->send();
    
                Logger::info("Rejection mail sent to $toname <$tomail>");
            } catch (Exception $e) {
                Logger::info("Rejection mail sent to $toname <$tomail>, error:" . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            Logger::error("Error sending rejection mail to $toname <$tomail>", $e->getMessage());
        }
    }

    /** Get the fields for templating
     * @param Mail $mail the message for which the fields should be returned
     * @return string[] associative array where the fields for templating are set
     */
    public function getFieldsForTemplate($mail) {
        $moderator = new Member($this->properties["i_moderator"]);

        // get the mail-specific fields
        $mail_fields = $mail->getFieldsForTemplate();

        // get the mailbox-specific fields
        $mailbox_fields = [
            "listaddress" => $this->properties["s_address"],
            "listname" => $this->properties["s_name"],
            "moderatorname" => "{$moderator->properties['s_name1']} {$moderator->properties['s_name2']}",
            "moderatoraddress" => $moderator->properties['s_email'],
        ];

        return array_merge($mail_fields, $mailbox_fields);
    }

    /** Format the subject of a message using the settings for this mailbox
     * @param Mail $mail the message for which the from-header has to be generated
     * @return string the formatted subject
     */
    public function formatSubject($mail) {
        $template = $this->properties["s_templatesubject"];

        // strip all newline tags from the template
        $template = str_replace("\n", "", $template);

        $fields = $this->getFieldsForTemplate($mail);
        return Util::formatTemplate(trim($template), $fields);
    }

    /** Format the from header of a message using the settings for this mailbox
     * @param Mail $mail the message for which the from-header has to be generated
     * @return string the formatted from-header
     */
    public function formatFrom($mail) {
        $template = $this->properties["s_templatefrom"];

        // strip all newline tags from the template
        $template = str_replace("\n", "", $template);

        $fields = $this->getFieldsForTemplate($mail);
        return Util::formatTemplate(trim($template), $fields);
    }

    /** Format the body of a message using the settings for this mailbox
     * @param Mail $mail the message for which the from-header has to be generated
     * @param boolean $html if true, the html body is returned, otherwise the plain text body
     * @return string the formatted body
     */
    public function formatBody($mail, $html = false) {
        $template = $this->properties["s_templatebody"];

        $fields = $this->getFieldsForTemplate($mail);

        // if html, convert newline tags to <br> and replace bodytext by html body
        if ($html) {
            $template = str_replace("\n", "<br>", $template);
            $fields["body"] = $mail->properties["s_bodyhtml"];
        }

        return Util::formatTemplate(trim($template), $fields);
    }

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
            'encryption'    => $this->getImapEncryption(),
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
            $mail = new Mail($i_mail = NULL, $message = $message);

            // if the mail was sent before the mailbox was created, move it to the processed folder without any other actions
            if ($mail->properties["d_sent"] < $this->properties["d_inserted"]) {
                $mail->processedAction();
                continue;
            }

            // set i_mailbox parameter
            $mail->properties["i_mailbox"] = $this->properties["i_mailbox"];

            // check if the mail is a bounce notification
            $bounce = $mail->parseBounce();
            if ($bounce) {
                $mail->properties["b_isbounce"] = true;
                Mail::markSentMailBounced($bounce["original_recipient"], $bounce["original_subject"]);
            }

            $mails[] = $mail;
        }

        return $mails;
    }

    /**
     * Get the allowed senders for this mailbox as member objects
     * 
     * @throws ValueError if s_allowedsenders is not a recognized value
     * @return array[Member] if allowed senders are found, empty array if no allowed senders are found, -1 if everybody is allowed
     */
    public function getAllowedSenders() {
        // if everybody is allowed, return true
        if ($this->properties['s_allowedsenders'] == 'everybody') {
            return -1;
        } else {
            switch ($this->properties['s_allowedsenders']) {
                case 'registered':
                    // only members registered in Mailiszt are allowed to address the list
                    return Member::getAll();
                case 'members':
                    // only members of the list allowed, return the recipients list
                    $allowed_ids = array_map(fn($m) => $m->properties['i_member'], $this->getRecipientsObjects());
                    return Member::getObjects($allowed_ids);
                case 'specific':
                    // specific people only allowed
                    $allowed_ids = json_decode($this->properties["j_allowedsenderspeople"]);
                    return Member::getObjects($allowed_ids);
                default:
                    // sender option not recognized, return throw error
                    Logger::error("s_allowedsenders $this->properties['s_allowedsenders'] for Mailbox $this->properties['i_mailbox'] is not a valid value");
                    throw new ValueError("s_allowedsenders $this->properties['s_allowedsenders'] for Mailbox $this->properties['i_mailbox'] is not a valid value");
            }
        }
    }

    /**
     * Get the recipients of this mailbox as member objects
     * This method should be replaced in future, when transitioning to object based models
     * 
     * @return array[Member]
     */
    public function getRecipientsObjects() {
        $recipients = $this->getRecipients();
        $recipients_ids = array_map(fn($m) => $m['i_member'], $recipients);
        return Member::getObjects($recipients_ids);
    }

    /**
     * Get the recipients of this mailbox as member as array (with keys i_member...)
     * 
     * @return array
     */
    public function getRecipients() {
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