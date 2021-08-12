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
        return [
            "***REMOVED***",
            "***REMOVED***"
        ];
    }


}