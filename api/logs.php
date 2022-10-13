<?php

include('includes.php');

use Steampixel\Route;

Route::add('/api/log', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $loggers = Logger::getAll((int)$_GET["limit"], (int)$_GET["offset"]);
        $apiInfo = array_map(fn($l) => $l->apiGetInfo($auth["s_role"], $fields), $loggers);
        return makeResponse($apiInfo);
    });
}, 'GET');