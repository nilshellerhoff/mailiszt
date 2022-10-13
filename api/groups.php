<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/group', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $groups = Group::getAll((int)$_GET["limit"], (int)$_GET["offset"]);
        $apiInfo = array_map(fn($g) => $g->apiGetInfo($auth["s_role"], $fields), $groups);
        return makeResponse($apiInfo);
    });
}, 'GET');

Route::add('/api/group/add', function() {
    return authenticatedAction(function($auth) {
        $group = new Group(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $group->save();
        return makeResponse($group->apiGetInfo($auth["s_role"]));
    });
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $fields = getFieldsForApi($_GET);
        $group = new Group((int)$i_group);
        return makeResponse($group->apiGetInfo($auth['s_role'], $fields));
    }, $i_group);
}, 'GET');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($i_group);
        $put = getPutData();
        unset($put['members']);
        $group->updateProperties($put);
        $group->save();
    }, $i_group);
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($i_group);
        $group->delete();
    }, $i_group);
}, 'DELETE');

// add routes for managing members of group
Route::add('/api/group/([0-9]*)/members', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($id = $i_group);
        return makeResponse($group->getMembers());
    }, $i_group);
}, 'GET');

Route::add('/api/group/([0-9]*)/members', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($id = $i_group);
        $group->setMembers(getPutData());
    }, $i_group);
}, 'PUT');