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

    $token = NULL;

    // try to get the auth token through authorization header (is probably destroyed by apache)
    // else get it through cookie (should work on http)
    $headers = getallheaders();
    if (array_key_exists('Authorization', $headers) && $headers['Authorization'] != '') {
        $token = $headers['Authorization'];
        $auth = User::checkAuthentication($token);
    } else if (isset($_COOKIE['accessToken']) && $_COOKIE['accessToken'] != '') {
        $token = $_COOKIE['accessToken'];
        $auth = User::checkAuthentication($token);
    } else if (isset($_COOKIE['auth']) && $_COOKIE['auth'] != '') {
        $token = $_COOKIE['auth'];
        $auth = User::checkAuthentication($token);
    }

    // $auth = User::checkAuthentication($token);
    if (isset($auth) && $auth) {
        $auth["s_role"] = 'ADMIN';
        return $auth;
    }
    return false;
}

function authenticatedAction($callback, ...$args) {
    // return the output of the callback if the user is authenticated
    $auth = checkAuthToken();
    if ($auth) {
        return $callback($auth, ...$args);
    } else {
        return makeResponse(["message" => "invalid authentication"], 403);
    }
}

/** Get the fields for templating
 * @param string[] $get the list of GET-parameter values
 * @return string[] array with the name of the fields
 */
function getFieldsForApi(array $get) {
    // if no fields are specified, return empty
    if (!isset($get['fields']) || $get['fields'] == '') {
        return [];
    }

    // else separate the fields parameter on commas and return the array
    $fields = explode(',', $get['fields']);
    $fields = array_map(fn($field) => trim($field), $fields);
    return $fields;
}

// include api definitions
include('members.php');
include('groups.php');
include('mailboxes.php');
include('user.php');
include('mail.php');
include('settings.php');
include('attachments.php');
include('logs.php');