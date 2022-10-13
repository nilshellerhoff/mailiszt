<?php

include('includes.php');

use Steampixel\Route;

Route::add('/api/setting/([a-zA-z0-9_]*)', function($settingName) {
    return authenticatedAction(function($auth, $settingName) {
        return makeResponse(
            data: [Setting::getValue($settingName)],
            code: 200,
            num_items: 1,
        );
    }, $settingName);
}, 'GET');

Route::add('/api/setting/([a-zA-z0-9]*)', function($settingName) {
    return authenticatedAction(function($auth, $settingName) {
        Setting::setValue($settingName, getPutData());
        return makeResponse(
            code: 200,
            message: "UPDATED_SETTING",
            message_long: "Setting '{$settingName}' updated.",
            num_items: 0,
        );
    }, $settingName);
}, 'PUT');