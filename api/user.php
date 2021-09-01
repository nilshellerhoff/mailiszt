<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/user/login', function() {
    $token = User::authenticate(getPutData()["username"], getPutData()["password"]);
    // sleep one second to prevent brute forcing -> this is bad, later implement a real rate limiter
    sleep(1);
    return makeResponse($token);
}, 'PUT');

Route::add('/api/user/logout', function() {
    $token = getPutData()["accessToken"];
    User::deleteToken($token);
});