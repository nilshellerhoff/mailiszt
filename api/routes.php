<?php

include('includes.php');

use Steampixel\Route;

// add access-control header to all requests
Route::add('/api/([a-z-0-9/]*)', function() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET,PUT,DELETE");
    header("Access-Control-Allow-Headers: *");
}, ['GET', 'PUT', 'DELETE', 'OPTIONS']);

// define some useful functions
function getPutData() {
    // return the json-decoded data from PUT
    return json_decode(file_get_contents('php://input'), true);
}

function makeResponse($data, $responseCode = 200, $content_type = 'application/json') {
    header("Content-Type: $content_type");
    http_response_code($responseCode);
    return json_encode($data);
}

function checkAuthToken() {
    // return the role of querying user using the supplied token (returns empty if invalid or no token)
    // right now there is only "ADMIN" or no auth
    $token = getallheaders()['Authorization'];
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