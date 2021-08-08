<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/group', function() {
    $db = new DB();
    $group_ids = $db->queryColumn("SELECT i_group FROM _group");
    $groups = [];
    foreach ($group_ids as $id) {
        $group = new Group($id = $id);
        $groups[] = $group->apiGetInfo("ADMIN");
    }
    return makeResponse($groups);
}, 'GET');

Route::add('/api/group/add', function() {
    $group = new Group(
        $id = null,
        $obj = null,
        $properties = getPutData()
    );
    $group->save();
    return makeResponse($group->apiGetInfo("ADMIN"));
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $group = new Group((int)$i_group);
    return makeResponse($group->apiGetInfo("ADMIN"));
}, 'GET');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $group = new Group($i_group);
    $group->updateProperties(getPutData());
    $group->save();
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    $group = new Group($i_group);
    $group->delete();
}, 'DELETE');

// add routes for managing members of group