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

    protected function populateFromObject() {
        $attributes = $this->object->getAttributes();

        $this->properties["s_subject"]      = $this->object->getSubject();
        $this->properties["s_frommail"]     = $attributes["from"]->toArray()[0]->toArray()["mail"];
        $this->properties["s_fromname"]     = $attributes["from"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->properties["s_replytomail"]  = $attributes["reply_to"]->toArray()[0]->toArray()["mail"];
        $this->properties["s_replytoname"]  = $attributes["reply_to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->properties["s_tomail"]       = $attributes["to"]->toArray()[0]->toArray()["mail"];
        $this->properties["s_toname"]       = $attributes["to"]->toArray()[0]->toArray()["personal"] ?: NULL;
        $this->properties["s_bodytext"]     = $this->object->getTextBody();
        $this->properties["s_bodyhtml"]     = $this->object->getHtmlBody();
        $this->properties["s_messageid"]    = $attributes["message_id"]->toString();
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
        // send this mail to all the addresses in mailbox
        $mail = new PHPMailer();

        $mail->isSMTP();                                        
        $mail->Host       = $mailbox->properties["s_smtpserver"];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailbox->properties["s_username"];
        $mail->Password   = $mailbox->properties["s_password"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailbox->properties["n_smtpport"];
        
        $mail->setFrom($mailbox->properties["s_address"], $mailbox->properties["s_name"]);

        $mail->Subject = $this->properties["s_subject"];

        if (isset($this->properties["s_bodyhtml"]) && !is_null($this->properties["s_bodyhtml"])) {
            // mail is html type
            $mail->isHTML(true);
            $mail->Body = $this->properties["s_bodyhtml"];
            $mail->AltBody = $this->properties["s_bodytext"];
        } else {
            // mail is only text type
            $mail->Body = $this->properties["s_bodytext"];
        }

        // add attachments to mail
        foreach ($this->attachments as $attachment) {
            $attach_path = ATTACHMENT_PATH . $attachment->properties["s_filename"];

            // if cid is set, handle as embedded image, else normal attachment
            if (isset($attachment->properties["s_cid"])) {
                echo "adding embedded image";
                $mail->AddEmbeddedImage(
                    $attach_path,
                    $attachment->properties["s_cid"],
                    $attachment->properties["s_name"]
                );
            } else {
                $mail->addAttachment($attach_path, $attachment->properties["s_name"]);
            }
        }

        // send the mail to all recipients in $mailbox
        foreach ($mailbox->getRecipients() as $recipient) {
            $mail->addAddress($recipient["s_email"]);
            $mail->send();
            $mail->clearAddresses();

            // mark mail as sent
            $this->markSentMail($recipient);
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
}