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

/**
 * Make an api response
 * 
 * Each api response is a json document structured like this:
 *  {
 *      "status": <<HTTP status code of the request>>,
 *      "status_long": <<human readable status>>,
 *      "message": <<capitalized, underscore short message of action>>
 *      "message_long": <<human readable message of action>>
 *      "num_items": <<number of items returned in this request>>
 *      "num_items_total": <<total number of items of the returned entity>>
 *      "data": <<array of the data returned>>
 *      "request_completed": <<timestamp of the completed request>>
 *  }
 * 
 * @param array $data array of the data returned
 * @param int $status status of the request (e.g. 200, 403)
 * @param string $message capitalized, underscore short message of action
 * @param string $message_long human readable message of action
 * @param int $num_items number of items returned in this request
 * @param int $num_items_total total number of items of the returned entity
 */
function makeResponse(array $data = null, int $code = 500, string $message = "", string $message_long = "", int $num_items = -1, int $num_items_total = -1) {
    $status = API_RESPONSE_STATUSES[$code];

    http_response_code($code);
    header("Content-Type: application/json");

    return json_encode([
        "code" => $code,
        "status" => $status,
        "message" => $message,
        "message_long" => $message_long,
        "num_items" => $num_items,
        "num_items_total" => $num_items_total,
        "data" => $data,
        "request_completed" => date(DATE_FORMAT),
    ]);
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