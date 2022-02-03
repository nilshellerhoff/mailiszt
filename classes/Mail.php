<?php

require_once('Base.php');

// php-mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail extends Base {
    public $table = "mail";
    public $identifier = "i_mail";

    public $attachments = [];
    public $saved;

    public $exposedInfo = [
        "ADMIN"     => [
            "i_mail",
            "i_mailbo",
            "s_internalid",
            "s_frommail",
            "s_fromname",
            "s_replytomail",
            "s_replytoname",
            "s_tomail",
            "s_toname",
            "s_messageid",
            "d_sent",
            "s_subject",
            "s_bodytext",
            "s_bodyhtml",
            "n_attachments",
            "d_forwarded",
            "d_inserted",
            "d_update",
        ]
    ];


    protected function populateFromObject() {
        $attributes = $this->object->getAttributes();

        $this->properties["s_subject"]      = $this->object->getSubject();
        $this->properties["s_frommail"]     = $attributes["from"]->toArray()[0]->toArray()["mail"];
        $this->properties["s_fromname"]     = $attributes["from"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->properties["s_replytomail"]  = $attributes["reply_to"] ? $attributes["reply_to"]->toArray()[0]->toArray()["mail"] : NULL;
        $this->properties["s_replytoname"]  = $attributes["reply_to"] ? $attributes["reply_to"]->toArray()[0]->toArray()["personal"] : NULL;
        $this->properties["s_tomail"]       = $attributes["to"]->toArray()[0]->toArray()["mail"];
        $this->properties["s_toname"]       = $attributes["to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->properties["s_bodytext"]     = $this->object->getTextBody();
        $this->properties["s_bodyhtml"]     = $this->object->getHtmlBody();
        $this->properties["s_messageid"]    = isset($attributes["message_id"]) ? $attributes["message_id"]->toString() : null;
        $this->properties["d_sent"]         = $attributes["date"]->toString();
        $this->properties["s_internalid"]   = $this->calculateHash();

        // check if mail is already present in DB
        $db = new DB();
        $mailid = $db->queryScalar(
            "SELECT i_mail FROM mail WHERE s_internalid = ?", 
            [$this->properties["s_internalid"]]
        );

        // if mail is present in DB, set dont save flag and populate i_mail
        if (isset($this->dontSave) && $this->dontSave) {
            $this->dontSave = true;
            $this->properties["i_mail"] = $mailid;
        }

        // crawl attachments
        $this->properties["n_attachments"] = 0;
        foreach ($this->object->getAttachments() as $key => $attachment) {
            // new attachment
            $this->attachments[] = new Attachment($i_attachment = NULL, $attachment);

            // update properties
            $end = count($this->attachments) - 1;            
            $this->attachments[$end]->properties["n_index"] =$this->properties["n_attachments"];
            $this->attachments[$end]->properties["s_filename"] = $this->properties["s_internalid"] . "_" . $this->properties["n_attachments"];

            $this->properties["n_attachments"]++;
        }
    }

    public function afterSave() {
        // save the attachments
        foreach ($this->attachments as $attachment) {
            $attachment->properties["i_mail"] = $this->properties["i_mail"];
            $attachment->save();
        }

        // move mail to folder or delete mail from INBOX
        if (MAIL_PROCESSED_ACTION == "move") {
            $this->object->move($folder_path = MAIL_PROCESSED_FOLDER);
        } else if (MAIL_PROCESSED_ACTION == "delete") {
            $this->object->delete($expunge = true);
        }
    }

    public function calculateHash() {
        // calculate a custom hash based on subject, sender, date and messageid (if present)
        // there doesn't seem to be a confirmation, that emails actually have unique IDs
        return hash('sha256', $this->properties["s_subject"] . $this->properties["s_frommail"] . $this->properties["d_sent"] . $this->properties["s_messageid"]);
    }

    public function forwardMail($mailbox) {
        // check if sender address is some automatic mailer deamon (delivery notifications) and exit
        if (preg_match('/(postmaster|mailer-daemon)@.*/', $this->properties["s_frommail"])) {
            return false;
        }

        // is the sender allowed to write to this mailinglist?
        if ($mailbox->properties['s_allowedsenders'] == 'everybody') {
            // everybody is allowed, continue
            Logger::log("forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is '{$mailbox->properties['s_allowedsenders']}'");
        } else {
            // not everybody is allowed, populate an array of allowed mails
            $allowed_mails = [];

            switch ($mailbox->properties['s_allowedsenders']) {
                case 'registered':
                    // only members registered in Mailiszt are allowed to address the list
                    $db = new DB();
                    $allowed_mails = $db->queryColumn('SELECT s_email FROM member');
                    break;
                case 'members':
                    // members allowed, check if sender is in recipients list
                    $allowed_mails = array_map(
                        function ($m) { return $m["s_email"]; },
                        $mailbox->getRecipients()
                    );
                    break;
                case 'specific':
                    // specific people only allowed
                    $allowed_ids = json_decode($mailbox->properties["j_allowedsenderspeople"]);
                    $allowed_mails = [];
                    foreach($allowed_ids as $id) {
                        $member = new Member($id);
                        $allowed_mails[] = $member->properties["s_email"];
                    }
                    break;
                }

            // check whether sender is in allowed mails
            if ( in_array($this->properties["s_frommail"], $allowed_mails) ) {
                Logger::log("forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is '{$mailbox->properties['s_allowedsenders']}' and '{$this->properties['s_frommail']}' is in " . json_encode($allowed_mails));
            } else {
                Logger::log("not forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is '{$mailbox->properties['s_allowedsenders']}' and '{$this->properties['s_frommail']}' is not in " . json_encode($allowed_mails));
                return false;
            }
        }

        // send this mail to all the addresses in mailbox
        $mail = new PHPMailer();

        try {
            $mail->isSMTP();                                        
            $mail->Host       = $mailbox->properties["s_smtpserver"];
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailbox->properties["s_username"];
            $mail->Password   = $mailbox->properties["s_password"];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $mailbox->properties["n_smtpport"];

            // allow the mail body to be empty
            $mail->AllowEmpty = true;

            // set charset to UTF-8 (this is a workaround for now, ideally we should use the charset from the mail)
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($mailbox->properties["s_address"], $mailbox->properties["s_name"]);

            $mail->Subject = $this->properties["s_subject"];

            if (isset($this->properties["s_bodyhtml"]) && !is_null($this->properties["s_bodyhtml"])) {
                // mail is html type
                $mail->isHTML(true);
                $mail->Body = $this->properties["s_bodyhtml"];
                $mail->AltBody = $this->properties["s_bodytext"];
            } else {
                // mail is only text type
                $mail->Body = $this->properties["s_bodytext"] ?? '';
            }

            // add attachments to mail
            foreach ($this->attachments as $attachment) {
                $attach_path = ATTACHMENT_PATH . $attachment->properties["s_filename"];

                // if cid is set, handle as embedded image, else normal attachment
                if (isset($attachment->properties["s_cid"])) {
                    $mail->AddEmbeddedImage(
                        $attach_path,
                        $attachment->properties["s_cid"],
                        $attachment->properties["s_name"]
                    );
                } else {
                    $mail->addAttachment($attach_path, $attachment->properties["s_name"]);
                }
            }

            // set the reply to address
            if ($mailbox->properties["s_replyto"] == 'sender') {
                $replyTos = [
                    [
                        "address" => $this->properties["s_frommail"],
                        "name" => $this->properties["s_fromname"]
                    ]
                ];
            } else if ($mailbox->properties["s_replyto"] == 'sender+mailinglist') {
                $replyTos = [
                    [
                        "address" => $this->properties["s_frommail"],
                        "name" => $this->properties["s_fromname"]
                    ],
                    [
                        "address" => $mailbox->properties["s_address"],
                        "name" => $mailbox->properties["s_name"]
                    ],
                ];
            }

            // if overridereplyto is true and a custom replyto mail is set, use that instead
            if ($mailbox->properties["b_overridereplyto"]) {
                if (( $this->properties["s_replytomail"] ?? '' ) != '') {
                    $replyTos = [
                        [
                            "address" => $this->properties["s_replytomail"],
                            "name" => $this->properties["s_replytoname"]
                        ]
                    ];
                }
            }

            if ($replyTos) {
                foreach ($replyTos as $replyTo) {
                    $mail->addReplyTo($replyTo["address"], $replyTo["name"]);
                }
            }

            // send the mail to all recipients in $mailbox
            foreach ($mailbox->getRecipients() as $recipient) {
                
                // don't send mail to the original sender if he is in the recipients list
                if ($recipient["s_email"] == $this->properties["s_frommail"]) {
                    continue;
                }

                // else send the mail to the recipient
                try {
                    $mail->addAddress($recipient["s_email"]);
                    $mail->send();
                    $mail->clearAddresses();
    
                    // mark mail as sent
                    $this->markSentMail($recipient);
    
                    Logger::log(sprintf("Mail %s sent to %s", $this->properties['s_subject'], $recipient["s_email"]));    
                } catch (Exception $e) {
                    Logger::log(sprintf("Mail %s could not be sent to %s, error: %s", $this->properties['s_subject'], $recipient["s_email"], $mail->ErrorInfo));
                }
            }
        } catch (Exception $e) {
            Logger::log(sprintf("Mail %s could not be sent, error: %s", $this->properties['s_subject'], $mail->ErrorInfo));
        }
    }

    private function markSentMail($recipient) {
        // insert a row into the mail2member table for each time a mail has been sent to a recipient
        $db = new DB();
        $db->insert(
            "mail2member",
            [
                "i_mail" => $this->properties["i_mail"],
                "i_member" => $recipient["i_member"],
                "d_sent" => date(DATE_FORMAT),
                "s_email" => $recipient["s_email"]
            ]
        );
    }

    public function getRecipients() {
        // get the recipients which a mail has been sent to
        $db = new DB();
        return $db->queryAll(
            "SELECT * FROM mail2member WHERE i_mail = ?",
            [$this->properties["i_mail"]]
        );
    }

    public function apiGetAddInfo($mail) {
        $mail["recipients"] = $this->getRecipients();
        return $mail;
    }
}