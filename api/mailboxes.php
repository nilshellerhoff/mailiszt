<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mailbox', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $db = new DB();
        $mailbox_ids = $db->queryColumn("SELECT i_mailbox FROM mailbox");
        $mailboxes = [];
        foreach ($mailbox_ids as $id) {
            $mailbox = new Mailbox($id = $id);
            $mailboxes[] = $mailbox->apiGetInfo("ADMIN");
        }
        return makeResponse($mailboxes);
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
        $mailbox = new Mailbox((int)$i_mailbox);
        return makeResponse($mailbox->apiGetInfo("ADMIN"));
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
        return makeResponse(Mailbox::getRecipientsCondition(getPutData()));
    }
}, 'PUT');

// actions for forwarding mails
Route::add('/api/mailbox/forward', function() {
    $db = new DB();
    $mailbox_ids = $db->queryColumn("SELECT i_mailbox FROM mailbox");
    foreach ($mailbox_ids as $id) {
        $mailbox = new Mailbox($id = $id);
        $mails = $mailbox->fetchMails();
        foreach ($mails as $mail) {
            $mail->save();
            $mail->forwardMail($mailbox);
        }
    }
});

Route::add('/api/mailbox/([0-9]*)/forward', function($i_mailbox) {
    $mailbox = new Mailbox($id = $i_mailbox);
    $mails = $mailbox->fetchMails();
    foreach ($mails as $mail) {
        $mail->save();
        $mail->forwardMail($mailbox);
    }
});