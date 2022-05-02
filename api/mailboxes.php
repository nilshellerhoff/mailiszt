<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mailbox', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mailboxes = Mailbox::getAll();
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mailboxes);
        return makeResponse($apiInfo);
    }
}, 'GET');

Route::add('/api/mailbox/add', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $mailbox->save();
        return makeResponse($mailbox->apiGetInfo("ADMIN"));
    }
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mailbox = new Mailbox((int)$i_mailbox);
        return makeResponse($mailbox->apiGetInfo("ADMIN", $fields));
    }
}, 'GET');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->updateProperties(getPutData());
        $mailbox->save();
    }
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->delete();
    }
}, 'DELETE');

// groups management
Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox($i_mailbox);
        return makeResponse($mailbox->getGroups());
    }
}, 'GET');

Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->setGroups(getPutData());
        $mailbox->save();
    }
}, 'PUT');

// getting recipients of mailbox
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox($i_mailbox);
        return makeResponse($mailbox->getRecipients());
    }
}, 'GET');

// getting recipients of mailbox with custom groups
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        return makeResponse($mailbox->getRecipients());
    }
}, 'PUT');

// actions for forwarding mails
Route::add('/api/mailbox/forward', function() {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::log("Email fetching is disabled", 'DEBUG');
        return;
    } else {
        $db = new DB();
        $mailbox_ids = $db->queryColumn("SELECT i_mailbox FROM mailbox");

        $mail_cnt = 0;
        foreach ($mailbox_ids as $id) {
            $mailbox = new Mailbox($id = $id);
            $mails = $mailbox->fetchMails();
            foreach ($mails as $mail) {
                $mail->save();
                $mail->forwardMail($mailbox);
                $mail_cnt++;
            }
        }
        Logger::log("Checked all mailboxes, found $mail_cnt mails to forward", 'DEBUG');
    }
});

Route::add('/api/mailbox/([0-9]*)/forward', function($i_mailbox) {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::log("Email fetching is disabled", 'DEBUG');
    } else {
        $mailbox = new Mailbox($id = $i_mailbox);
        $mails = $mailbox->fetchMails();

        $mail_cnt = 0;
        foreach ($mails as $mail) {
            $mail->save();
            $mail->forwardMail($mailbox);
            $mail_cnt++;
        }
        Logger::log("Checked mailbox $i_mailbox, found $mail_cnt mails to forward", 'DEBUG');
    }
});