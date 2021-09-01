<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/users/current/login', function() {
    $token = User::authenticate(getPutData()["username"], getPutData()["password"]);
    // sleep one second to prevent brute forcing -> this is bad, later implement a real rate limiter
    sleep(1);
    return makeResponse($token);
}, 'PUT');

Route::add('/api/users/current/logout', function() {
    $token = getPutData()["accessToken"];
    User::deleteToken($token);
}, 'PUT');

Route::add('/api/users/current', function() {
    // return info about the current user
    $userid = User::idFromToken(getPutData()["accessToken"]);
    $user = new User($userid);
    return makeResponse($user->apiGetInfo("ADMIN"));
}, 'PUT');