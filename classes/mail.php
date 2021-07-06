<?php

class Mail {
    public $properties;
    public $attachments = [];
    public static $validationrules = [];

    public $saved;

    function __construct() {
    }

    public function populateFromImap($mailbox, $message) {
        $attributes = $message->getAttributes();

        $this->i_mailbox    = $mailbox["i_mailbox"];
        $this->subject      = $message->getSubject();
        $this->frommail     = $attributes["from"]->toArray()[0]->toArray()["mail"];
        $this->fromname     = $attributes["from"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->replytomail  = $attributes["reply_to"]->toArray()[0]->toArray()["mail"];
        $this->replytoname  = $attributes["reply_to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->tomail       = $attributes["to"]->toArray()[0]->toArray()["mail"];
        $this->toname       = $attributes["to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->bodytext     = $message->getTextBody();
        $this->bodyhtml     = $message->getHtmlBody();
        $this->messageid    = $attributes["message_id"]->toString();
        $this->sent         = $attributes["date"]->toString();
        $this->internalid   = $this->calculateHash();

        // check if messageid is already present in DB
        $db = new DB();
        $this->saved = $db->queryScalar(
            "SELECT 1 FROM mail WHERE s_internalid = ?", 
            [$this->internalid]
        );

        // crawl attachments
        $attach_cnt = 0;
        foreach ($message->getAttachments() as $key => $attachment) {
            // new attachment
            $this->attachments[] = new Attachment($i_attachment = NULL, $attachment);

            // update properties
            $end = count($this->attachments) - 1;            
            $this->attachments[$end]->properties["n_index"] = $attach_cnt;
            $this->attachments[$end]->properties["s_filename"] = $this->internalid . "_" . $attach_cnt;

            $this->attachments[$end]->saveAttachment($attachment);

            $attach_cnt++;
        }
    }

    public function calculateHash() {
        return hash('sha256', $this->subject . $this->frommail . $this->sent . $this->messageid);
    }

    public function writeToDB() {
        if ($this->saved) {
            return ;
        }

        $db = new DB();

        $this->i_mail = $db->insert(
            "mail",
            [
                "i_mailbox"     => $this->i_mailbox,
                "s_internalid"  => $this->internalid,
                "s_subject"     => $this->subject,
                "s_frommail"    => $this->frommail,
                "s_fromname"    => $this->fromname,
                "s_replytomail" => $this->replytomail,
                "s_replytoname" => $this->replytoname,
                "s_tomail"      => $this->tomail,
                "s_toname"      => $this->toname,
                "s_bodytext"    => $this->bodytext,
                "s_bodyhtml"    => $this->bodyhtml,
                "s_messageid"   => $this->messageid,
                "d_sent"        => $this->sent
            ]
        );

        foreach ($this->attachments as $attachment) {
            $attachment->properties["i_mail"] = $this->i_mail;
            $attachment->save();
        }
    }

    public function saveAttachments() {
    //     foreach ($message->getAttachments() )
    }
}