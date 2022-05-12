<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mail', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mails = Mail::getAll();
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mails);
        return makeResponse($apiInfo);
    }
}, 'GET');

Route::add('/api/mail/([0-9]*)', function($i_mail) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $mail = new Mail($i_mail);
        return makeResponse($mail->apiGetInfo("ADMIN", $fields));
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