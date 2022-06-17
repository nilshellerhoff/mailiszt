<?php

require_once('Base.php');

// php-mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail extends Base {
    public static $table = "mail";
    public static $identifier = "i_mail";

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
            "b_isbounce",
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
        $this->properties["d_sent"]         = $attributes["date"]->toDate()->utc()->format(DATE_FORMAT);
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
        $this->processedAction();
    }

    /** Perform the processed action on the mail defined in MAIL_PROCESSED_ACTION
     */
    public function processedAction() {
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
        $allowed_members = $mailbox->getAllowedSenders();
        if ($allowed_members === -1) {
            // everybody is allowed, continue
            Logger::log("forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is 'everybody'");
        } else {
            // not everybody is allowed, populate an array of allowed mails
            $allowed_mails = array_map(fn($m) => $m->properties["s_email"], $allowed_members);

            // check whether sender is in allowed mails
            if ( in_array($this->properties["s_frommail"], $allowed_mails) ) {
                Logger::log("forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is '{$mailbox->properties['s_allowedsenders']}' and '{$this->properties['s_frommail']}' is in " . json_encode($allowed_mails));
            } else {
                Logger::log("not forwarding mail \"{$this->properties['s_subject']}\" because allowed senders is '{$mailbox->properties['s_allowedsenders']}' and '{$this->properties['s_frommail']}' is not in " . json_encode($allowed_mails));
                $format_parameters = [
                    "sender" => $this->properties["s_frommail"],
                    "mailinglist" => $mailbox->properties["s_address"],
                    "moderator" => (new Member($mailbox->properties["i_moderator"]))->properties["s_email"]
                ];
                $mailbox->sendMail(
                    $this->properties["s_frommail"],
                    $this->properties["s_fromname"],
                    "RE: " . $this->properties["s_subject"],
                    Util::formatTemplate(REJECTION_MAIL_TEXT, $format_parameters),
                );
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
            $mail->SMTPSecure = $mailbox->getSmtpEncryption();
            $mail->Port       = $mailbox->properties["n_smtpport"];

            // set the X-Mailer header field
            $mail->XMailer = X_MAILER_HEADER;

            // allow the mail body to be empty
            $mail->AllowEmpty = true;

            // set charset to UTF-8 (this is a workaround for now, ideally we should use the charset from the mail)
            $mail->CharSet = 'UTF-8';
            
            $fromname = $mailbox->formatFrom($this);
            $mail->setFrom($mailbox->properties["s_address"], $fromname);

            $mail->Subject = $mailbox->formatSubject($this);

            if ($this->isHTML()) {
                // mail is html type
                $mail->isHTML(true);
                $mail->Body = $mailbox->formatBody($this, true);
                $mail->AltBody = $mailbox->formatBody($this, false);
            } else {
                // mail is only text type
                $mail->Body = $mailbox->formatBody($this, false) ?? '';
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

            $replyTos = $mailbox->getReplyToAddresses(
                $this->properties["s_frommail"],
                $this->properties["s_fromname"],
                $this->properties["s_replytomail"],
                $this->properties["s_replytoname"],
            );

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

    /** Parse the mail for signs that it is a bounce mail
     * @return Boolean|Array Returns false if the mail is not a bounce mail, otherwise returns an array with the following keys:
     * - bounce_email: the email address which sent the bounce
     * - bounce_subject: the subject of the bounce mail
     * - bounce_body: the body of the bounce mail
     * - original_subject: the subject of the bounced mail
     * - original_recipient: the recipient of the bounced mail
     */
    public function parseBounce() {
        // we check if the sending address is either postmaster or mailer-daemon, if yes it is a bounce mail
        if (preg_match('/(postmaster|mailer-daemon)@.*/', $this->properties["s_frommail"])) { 

            // get the original subject of the mail
            if (preg_match('/Rejected: (.*)/', $this->properties["s_subject"], $matches)) {
                $original_subject = $matches[1];
            } else if (preg_match('/Subject:\s*([^\n]*)/', $this->properties["s_bodytext"], $matches)) {
                $original_subject = $matches[1];
            } else {
                $original_subject = NULL;
            }

            // get the original sender of the mail
            if (preg_match('/The following address failed:\s*([^\s@]+@[^\s@:]+)/', $this->properties["s_bodytext"], $matches)) {
                $original_recipient = $matches[1];
            } else if (preg_match('/To:\s*([^\s@]+@[^\s@]+)/', $this->properties["s_bodytext"], $matches)) {
                $original_recipient = $matches[1];
            } else {
                $original_recipient = NULL;
            }
                    
            return [
                "bounce_email" => $this->properties["s_frommail"],
                "bounce_subject" => $this->properties["s_subject"],
                "bounce_body" => $this->properties["s_bodytext"],
                "original_subject" => trim($original_subject),
                "original_recipient" => trim($original_recipient),
            ];
        }
        return false;
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

   /** Find and mark a mail as bounced in the mail2member table
    * @param String $address the email address which triggered the bounce
    * @param String $subject the subject of the mail which triggered the bounce
    * @return Int|Boolean if successfull, id of the entry in mail2member, false otherwise
    */
    public static function markSentMailBounced(string $address, string $subject) {
        // if either address or subject are not set, return false
        if (!isset($address) || !isset($subject)) {
            return false;
        }

        // we search for the mail in the mail2member table based on the sender address and the subject
        $db = new DB();
        try {
            $mail2memberID = $db->queryScalar(
                "SELECT i_mail2member FROM mail2member mm INNER JOIN mail m on m.i_mail = mm.i_mail WHERE mm.s_email = :email AND m.s_subject = :subject",
                ["email" => $address, "subject" => $subject]
            );
            $db->update("mail2member", ["b_bounced" => true], ["i_mail2member" => $mail2memberID]);

            return $mail2memberID;
        } catch (Exception $e) {
            return false;
        }
    }

    /** Check if this mail is an HTML mail
     * @return Boolean true if HTML mail, false otherwise
     */
    public function isHTML() {
        return (isset($this->properties["s_bodyhtml"]) && !is_null($this->properties["s_bodyhtml"]));
    }

    /** Get the mail-specific fields for templating
     * @return string[] associative array where the fields for templating are set
     */
    public function getFieldsForTemplate() {
        $fields = [
            "subject" => $this->properties["s_subject"],
            "body" => $this->properties["s_bodytext"],
            "frommail" => $this->properties["s_frommail"],
            "fromname" => $this->properties["s_fromname"],
        ];
        return $fields;
    }

    public function getRecipients() {
        // get the recipients which a mail has been sent to
        $db = new DB();
        return $db->queryAll(
            "SELECT * FROM mail2member WHERE i_mail = ?",
            [$this->properties["i_mail"]]
        );
    }

    public function getAttachments() {
        $db = new DB();
        $attachment_ids = $db->queryColumn("SELECT i_attachment FROM attachment WHERE i_mail = ?", [$this->properties["i_mail"]]);
        return Attachment::getObjects($attachment_ids);
    }

    public function apiGetAddInfo($role, $mail, $fields) {
        $add_fields = ["recipients", "num_recipients", "attachments"];

        $desired_fields = $fields ? $fields : $add_fields;
        $return_fields = array_intersect($desired_fields, $add_fields);

        if (in_array("recipients", $return_fields)) {
            $mail["recipients"] = $this->getRecipients();
        }
        if (in_array("num_recipients", $return_fields)) {
            $mail["num_recipients"] = count($this->getRecipients());
        }

        if (in_array("attachments", $return_fields)) {
            $attachments = $this->getAttachments();
            $mail["attachments"] = array_map(fn($a) => $a->apiGetInfo($role),  $attachments);
        }

        return $mail;
    }
}