<?php

include('includes.php');

use Steampixel\Route;

Route::add('/api/setting/([a-zA-z0-9_]*)', function($settingName) {
    return authenticatedAction(function($auth, $settingName) {
        return makeResponse(Setting::getValue($settingName));
    }, $settingName);
}, 'GET');

Route::add('/api/setting/([a-zA-z0-9]*)', function($settingName) {
    return authenticatedAction(function($auth, $settingName) {
        Setting::setValue($settingName, getPutData());
    }, $settingName);
}, 'PUT');