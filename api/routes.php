<?php

include('includes.php');

use Steampixel\Route;

// add access-control header to all requests
Route::add('/api/([a-z-0-9/_]*)', function() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET,PUT,DELETE");
    header("Access-Control-Allow-Headers: *");
}, ['GET', 'PUT', 'DELETE', 'OPTIONS']);

// define some useful functions
function getPutData() {
    // return the json-decoded data from PUT
    return json_decode(file_get_contents('php://input'), true);
}

function makeResponse($data, $responseCode = 200, $type = 'json') {
    http_response_code($responseCode);
    if ($type == 'json') {
        header("Content-Type: application/json");
        return json_encode($data);
    }
    else if ($type == 'text') {
        header("Content-Type 'text/plain'");
        return $data;
    }
}

function checkAuthToken() {
    // return the role of querying user using the supplied token (returns empty if invalid or no token)
    // right now there is only "ADMIN" or no auth

    // try to get the auth token through authorization header (is probably destroyed by apache)
    // else get it through cookie (should work on http)
    $headers = getallheaders();
    if (array_key_exists('Authorization', $headers)) {
        $token = $headers['Authorization'];
    } else {
        $token = $_COOKIE['accessToken'];
    }
    
    $auth = User::checkAuthentication($token);
    if ($auth) {
        $auth["s_role"] = 'ADMIN';
        return $auth;
    }
    return false;
}

// include api definitions
include('members.php');
include('groups.php');
include('mailboxes.php');
include('user.php');
include('mail.php');
include('settings.php');
include('attachments.php');