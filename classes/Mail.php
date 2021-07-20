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
        echo "populating";
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
        $this->dontSave = $db->queryScalar(
            "SELECT 1 FROM mail WHERE s_internalid = ?", 
            [$this->properties["s_internalid"]]
        );

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
            $mail->Body = $this->properties["s_bodytext"];
        }

        foreach ($mailbox->getRecipients() as $address) {
            $mail->addAddress($address);
            $mail->send();
            $mail->clearAddresses();
        }
    }
}




// //Recipients
// $mail->setFrom($username, 'Mailer');
// $mail->addAddress('***REMOVED***', 'Nils Hellerhoff');     //Add a recipient
// // $mail->addAddress('ellen@example.com');               //Name is optional
// // $mail->addReplyTo('info@example.com', 'Information');
// // $mail->addCC('cc@example.com');
// // $mail->addBCC('bcc@example.com');

// //Attachments
// // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
// // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

// //Content
// $mail->isHTML(true);                                  //Set email format to HTML
// $mail->Subject = $imap_mail["subject"];
// if (isset($imap_mail["html"])) {
//     $mail->Body    = $imap_mail["html"];
//     $mail->AltBody = $imap_mail["text"];
// } else {
//     $mail->Body = $imap_mail["text"];
// }

// $attachment_path = './tmp/attachments/' . hash('md5', $imap_mail["subject"],false) . '/';
// for ($idx=0; $idx<$imap_mail["num_attachments"]; $idx++) {
//     $attach_name = scandir($attachment_path . $idx . '/')[2];
//     // var_dump(scandir($attachment_path . $idx . '/'));
//     // die();
//     echo 'adding attachment ' . $attachment_path . $idx . '/' . $attach_name . '<br>';
//     $mail->addAttachment($attachment_path . $idx . '/' . $attach_name);
// }

// $mail->send();
// echo 'Message has been sent, ' . $imap_mail["num_attachments"] . ' attachments<br><br>';
// } catch (Exception $e) {
// echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} <br><br>";
// }
