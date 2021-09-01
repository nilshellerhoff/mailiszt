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

function makeResponse($data, $responseCode = 200) {
    http_response_code($responseCode);
    return json_encode($data);
}

function checkAuthentication() {
    // return the role of querying user
    // todo
    return 'ADMIN';
}

// include api definitions
include('members.php');
include('groups.php');
include('mailboxes.php');
include('user.php');