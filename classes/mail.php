<?php

class Mail {
    public $i_mail;
    public $i_mailbox;
    public $subject;
    public $frommail;
    public $fromname;
    public $tomail;
    public $toname;
    public $bodytext;
    public $bodyhtml;
    public $messageid;
    public $sent;
    public $attachments;

    function __construct() {
    }

    public function populateFromImap($mailbox, $message) {
        $attributes = $message->getAttributes();

        $this->i_mailbox    = $mailbox["i_mailbox"];
        $this->subject      = $message->getSubject();
        $this->frommail     = $attributes["from"]->toArray()[0]->toArray()["mail"];
        $this->fromname     = $attributes["from"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->tomail       = $attributes["to"]->toArray()[0]->toArray()["mail"];
        $this->toname       = $attributes["to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->bodytext     = $message->getTextBoxy();
        $this->bodyhtml     = $message->getHtmlBody();
        $this->messageid    = $attributes["message_id"]->toString();
        $this->sent         = $attributes["date"]->toString();

        $attach_cnt = 0;
        foreach ($message->getAttachments() as $key => $attachment) {
            $this->attachments[] = [
                "index"         => $attach_cnt,
                "content_type"  => $attachment->getAttributes()["content_type"],
                "cid"           => $attachment->getAttributes()["id"],
                "filename"      => $attachment->getAttributes()["name"],
                "i_attachment"  => NULL,
            ];
            $attachment->save('data/attachments/', $filename = $this->calculateHash() . '_' . $attach_cnt);
            $attach_cnt++;
        }
    }

    public function calculateHash() {
        return hash('sha256', $this->subject . $this->frommail . $this->sent . $this->messageid);
    }

    public function writeToDB() {
        $db = new DB();
        $this->i_mail = $db->insert(
            "mail",
            [
                "i_mailbox"     => $this->i_mailbox,
                "s_subject"     => $this->subject,
                "s_frommail"    => $this->frommail,
                "s_fromname"    => $this->fromname,
                "s_tomail"      => $this->tomail,
                "s_toname"      => $this->toname,
                "s_bodytext"    => $this->bodytext,
                "s_bodyhtml"    => $this->bodyhtml,
                "s_messageid"   => $this->messageid,
                "d_sent"        => $this->sent
            ]
        );

        foreach ($this->attachments as $attachment) {
            $attachment["i_attachment"] = $db->insert(
                "attachment",
                [
                    "i_mail"        => $this->i_mail,
                    "s_contenttype" => $attachment["content_type"],
                    "s_cid"         => $attachment["cid"],
                    "s_filename"    => $attachment["filename"],
                    "n_index"       => $attachment["index"]
                ]
            );
        }
    }

    public function saveAttachments() {
    //     foreach ($message->getAttachments() )
    }
}