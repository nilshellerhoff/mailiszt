<?php

include('includes.php');

use Steampixel\Route;

Route::add('/api/log', function() {
    return authenticatedAction(function($auth) {
        $fields = array_filter(explode(",", $_GET['fields']));
        $loggers = Logger::getAll();
        $apiInfo = array_map(fn($l) => $l->apiGetInfo($auth["s_role"], $fields), $loggers);
        return makeResponse($apiInfo);
    });
}, 'GET');