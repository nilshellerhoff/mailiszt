<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/group', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $groups = Group::getAll(...getLimitForApi($_GET));
        $apiInfo = array_map(fn($g) => $g->apiGetInfo($auth["s_role"], $fields), $groups);
        return makeResponse(
            data: $apiInfo,
            code: 200,
            num_items: count($apiInfo),
            num_items_total: Group::getObjectsCount(),
        );
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
        return makeResponse(
            data: $group->apiGetInfo($auth["s_role"]),
            code: 200,
            message: "CREATED_GROUP",
            message_long: "Group '{$group->properties['s_name']}' created.",
        );
    });
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $fields = getFieldsForApi($_GET);
        $group = new Group((int)$i_group);
        return makeResponse(
            data: [$group->apiGetInfo($auth['s_role'], $fields)],
            code: 200
        );
    }, $i_group);
}, 'GET');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($i_group);
        $put = getPutData();
        unset($put['members']);
        $group->updateProperties($put);
        $group->save();
        return makeResponse(
            code: 200,
            message: "UPDATED_GROUP",
            message_long: "Group '{$group->properties['s_name']}' updated.",
        );
    }, $i_group);
}, 'PUT');

Route::add('/api/group/([0-9]*)', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($i_group);
        $group->delete();
        return makeResponse(
            code: 200,
            message: "DELETED_GROUP",
            message_long: "Group '{$group->properties['s_name']}' deleted.",
        );
    }, $i_group);
}, 'DELETE');

// add routes for managing members of group
Route::add('/api/group/([0-9]*)/members', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($id = $i_group);
        return makeResponse(
            code: 200,
            data: $group->getMembers()
        );
    }, $i_group);
}, 'GET');

Route::add('/api/group/([0-9]*)/members', function($i_group) {
    return authenticatedAction(function($auth, $i_group) {
        $group = new Group($id = $i_group);
        $group->setMembers(getPutData());
        return makeResponse(
            code: 200,
            message: "UPDATED_GROUP_MEMBERS",
            message_long: "Group '{$group->properties['s_name']}' members updated.",
        );
    }, $i_group);
}, 'PUT');