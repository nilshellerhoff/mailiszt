<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mail', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $db = new DB();
        $mails = [];
        $mailids = $db->queryColumn("SELECT i_mail FROM mail ORDER BY i_mail desc");
        foreach ($mailids as $mailid) {
            $mail = new Mail($mailid);
            $mails[] = $mail->apiGetInfo("ADMIN");
        }
        return makeResponse($mails);
    }
}, 'GET');

Route::add('/api/mail/([0-9]*)', function($i_mail) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mail = new Mail($i_mail);
        return makeResponse($mail->apiGetInfo("ADMIN"));
    }
}, 'GET');

Route::add('/api/mail/([0-9]*)/recipient', function($i_mail) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $mail = new Mail($i_mail);
        return makeResponse($mail->getRecipients());
    }
}, 'GET');