<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mailbox', function() {
    return authenticatedAction(function($auth) {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mailboxes = Mailbox::getAll();
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mailboxes);
        return makeResponse($apiInfo);
    });
}, 'GET');

Route::add('/api/mailbox/add', function() {
    return authenticatedAction(function($auth) {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $mailbox->save();
        return makeResponse($mailbox->apiGetInfo("ADMIN"));
    });
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mailbox = new Mailbox((int)$i_mailbox);
        return makeResponse($mailbox->apiGetInfo("ADMIN", $fields));
    }, $i_mailbox);
}, 'GET');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->updateProperties(getPutData());
        $mailbox->save();
    }, $i_mailbox);
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->delete();
    }, $i_mailbox);
}, 'DELETE');

// groups management
Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        return makeResponse($mailbox->getGroups());
    }, $i_mailbox);
}, 'GET');

Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->setGroups(getPutData());
        $mailbox->save();
    }, $i_mailbox);
}, 'PUT');

// getting recipients of mailbox
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        return makeResponse($mailbox->getRecipients());
    }, $i_mailbox);
}, 'GET');

// getting recipients of mailbox with custom groups
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        return makeResponse($mailbox->getRecipients());
    }, $i_mailbox);
}, 'PUT');

// actions for forwarding mails
Route::add('/api/mailbox/forward', function() {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::log("Email fetching is disabled", 'INFO');
        return;
    } else {
        $mailboxes = Mailbox::getAll();
        $mail_cnt = 0;
        $mailbox_cnt = 0;

        foreach ($mailboxes as $mailbox) {
            try {
                $mails = $mailbox->fetchMails();
                foreach ($mails as $mail) {
                    try {
                        $mail->save();
                        $mail->forwardMail($mailbox);
                        $mail_cnt++;        
                    } catch (\Throwable $e) {
                        Logger::log("could not forward mail i_mail = {$mail->properties['i_mail']}. Error: {$e->getMessage()}", "WARNING");
                    }
                }
                $mailbox_cnt++;
            } catch (\Throwable $e) {
                Logger::log("could not check mailbox i_mailbox = {$mailbox->properties['i_mailbox']}. Error: {$e->getMessage()}", "WARNING");
            }
        }

        Logger::log("Checked {$mailbox_cnt} mailboxes, found $mail_cnt mails to forward", 'DEBUG');
    }
});

Route::add('/api/mailbox/([0-9]*)/forward', function($i_mailbox) {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::log("Email fetching is disabled", 'INFO');
    } else {
        $mail_cnt = 0;
        $mailbox = new Mailbox($id = $i_mailbox);

        try {   
            $mails = $mailbox->fetchMails();
            foreach ($mails as $mail) {
                try {
                    $mail->save();
                    $mail->forwardMail($mailbox);
                    $mail_cnt++;        
                } catch (\Throwable $e) {
                    Logger::log("could not forward mail i_mail = {$mail->properties['i_mail']}. Error: {$e->getMessage()}", "WARNING");
                }
            }
            Logger::log("Checked mailbox i_mailbox = {$mailbox->properties['i_mailbox']}, found $mail_cnt mails to forward", 'DEBUG');

        } catch (\Throwable $e) {
            Logger::log("could not check mailbox i_mailbox = {$mailbox->properties['i_mailbox']}. Error: {$e->getMessage()}", "WARNING");
        }
    }
});