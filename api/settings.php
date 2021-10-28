<?php

include('includes.php');

use Steampixel\Route;

Route::add('/api/setting/([a-zA-z0-9_]*)', function($settingName) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        return makeResponse(Setting::getValue($settingName));
    }
}, 'GET');

Route::add('/api/setting/([a-zA-z0-9]*)', function($settingName) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        Setting::setValue($settingName, getPutData());
    }
}, 'PUT');