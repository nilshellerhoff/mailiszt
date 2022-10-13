<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mail', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $mails = Mail::getAll((int)$_GET["limit"], (int)$_GET["offset"]);
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mails);
        return makeResponse($apiInfo);
    });
}, 'GET');

Route::add('/api/mail/([0-9]*)', function($i_mail) {
    return authenticatedAction(function($auth, $i_mail) {
        $fields = getFieldsForApi($_GET);
        $mail = new Mail($i_mail);
        return makeResponse($mail->apiGetInfo("ADMIN", $fields));
    }, $i_mail);
}, 'GET');

Route::add('/api/mail/([0-9]*)/recipient', function($i_mail) {
    return authenticatedAction(function($auth, $i_mail) {
        $mail = new Mail($i_mail);
        return makeResponse($mail->getRecipients());
    }, $i_mail);
}, 'GET');