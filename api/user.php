<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/users/current/login', function() {
    $token = User::authenticate(getPutData()["username"], getPutData()["password"]);
    // sleep one second to prevent brute forcing -> this is bad, later implement a real rate limiter
    sleep(1);
    if ($token) {
        setcookie('auth', $token, $options = [
            'expires' => time() + AUTH_TOKEN_LIFETIME,
            'path' => '/',
        ]);
        return makeResponse($token);
    } else {
        return makeResponse('invalid authentication', 403);
    }
}, 'PUT');

Route::add('/api/users/current/logout', function() {
    return authenticatedAction(function($auth) {
        User::deleteToken($auth["s_token"]);
    });
}, 'PUT');

Route::add('/api/users/current', function() {
    // return info about the current user
    return authenticatedAction(function($auth) {
        $userid = $auth["i_user"];
        $user = new User($userid);
        return makeResponse($user->apiGetInfo($auth["s_role"])); 
    });
}, 'PUT');

Route::add('/api/users/current/password', function() {
    return authenticatedAction(function($auth) {
        $put_data = getPutData();

        $oldPass =  $put_data["oldPassword"];
        $newPass1 = $put_data["newPassword1"];
        $newPass2 = $put_data["newPassword2"];

        if ($newPass1 != $newPass2) {
            return makeResponse("passwords do not match", 400);
        }

        $userid = User::idFromToken($put_data["accessToken"]);
        $user = new User($userid);

        if (!$user->checkPassword($userid, $oldPass)) {
            return makeResponse("wrong old password", 403);
        }

        $user->updatePassword($newPass1);
        $user->save();

        // return "password changed to '$newPass1'";
        return makeResponse("password updated", 200);
    });
}, 'PUT');

// Route::add('/api/users/([0-9]*)/password', function($userid) {
//     $put_data = getPutData();

//     $newPass = $put_data["newPassword2"];

//     $user = new User($userid);

//     $user->updatePassword($newPass);
//     $user->save();

//     return "password changed to '$newPass'";
// }, 'PUT');