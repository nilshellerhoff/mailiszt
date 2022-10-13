<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mail', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $mails = Mail::getAll((int)$_GET["limit"], (int)$_GET["offset"]);
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mails);
        return makeResponse(
            data: $apiInfo,
            code: 200,
            num_items: count($apiInfo),
            num_items_total: Mail::getObjectsCount(),
        );    
    });
}, 'GET');

Route::add('/api/mail/([0-9]*)', function($i_mail) {
    return authenticatedAction(function($auth, $i_mail) {
        $fields = getFieldsForApi($_GET);
        $mail = new Mail($i_mail);
        return makeResponse(
            data: [$mail->apiGetInfo("ADMIN", $fields)],
            code: 200,
            num_items: 1,
            num_items_total: Mail::getObjectsCount(),
        );
    }, $i_mail);
}, 'GET');

Route::add('/api/mail/([0-9]*)/recipient', function($i_mail) {
    return authenticatedAction(function($auth, $i_mail) {
        $mail = new Mail($i_mail);
        return makeResponse(
            data: [$mail->getRecipients()],
            code: 200,
            num_items: 1,
            num_items_total: Mail::getObjectsCount(),
        );
    }, $i_mail);
}, 'GET');