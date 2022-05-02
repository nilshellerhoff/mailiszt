<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/group', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $db = new DB();
        $group_ids = $db->queryColumn("SELECT i_group FROM _group");
        $groups = [];
        foreach ($group_ids as $id) {
            $group = new Group($id = $id);
            $groups[] = $group->apiGetInfo($auth["s_role"], $fields);
        }
        return makeResponse($groups);
    }
}, 'GET');

Route::add('/api/group/add', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $group = new Group(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $group->save();
        return makeResponse($group->apiGetInfo($auth["s_role"]));
    }
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $group = new Group((int)$i_group);
        return makeResponse($group->apiGetInfo("ADMIN", $fields));
    }
}, 'GET');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $group = new Group($i_group);
        $put = getPutData();
        unset($put['members']);
        $group->updateProperties($put);
        $group->save();
    }
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $group = new Group($i_group);
        $group->delete();
    }
}, 'DELETE');

// add routes for managing members of group
Route::add('/api/group/([0-9]*)/members', function($i_group) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $group = new Group($id = $i_group);
        return makeResponse($group->getMembers());
    }
}, 'GET');

Route::add('/api/group/([0-9]*)/members', function($i_group) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $group = new Group($id = $i_group);
        $group->setMembers(getPutData());
    }
}, 'PUT');