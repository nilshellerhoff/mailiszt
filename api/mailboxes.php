<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mailbox', function() {
    $db = new DB();
    $mailbox_ids = $db->queryColumn("SELECT i_mailbox FROM mailbox");
    $mailboxes = [];
    foreach ($mailbox_ids as $id) {
        $mailbox = new Mailbox($id = $id);
        $mailboxes[] = $mailbox->apiGetInfo("ADMIN");
    }
    return makeResponse($mailboxes);
}, 'GET');

Route::add('/api/mailbox/add', function() {
    $mailbox = new Mailbox(
        $id = null,
        $obj = null,
        $properties = getPutData()
    );
    $mailbox->save();
    return makeResponse($mailbox->apiGetInfo("ADMIN"));
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $mailbox = new Mailbox((int)$i_mailbox);
    return makeResponse($mailbox->apiGetInfo("ADMIN"));
}, 'GET');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $mailbox = new Mailbox($i_mailbox);
    $mailbox->updateProperties(getPutData());
    $mailbox->save();
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    $mailbox = new Mailbox($i_mailbox);
    $mailbox->delete();
}, 'DELETE');

Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    $mailbox = new Mailbox($i_mailbox);
    return makeResponse($mailbox->getGroups());
}, 'GET');

Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    $mailbox = new Mailbox($i_mailbox);
    $mailbox->setGroups(getPutData());
    $mailbox->save();
}, 'PUT');