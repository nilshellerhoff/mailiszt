<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/member', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $members = Member::getAll(...getLimitForApi($_GET));
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $members);
        return makeResponse(
            data: $apiInfo,
            code: 200,
            num_items: count($apiInfo),
            num_items_total: Member::getObjectsCount(),
        );
    });
}, 'GET');

Route::add('/api/member/add', function() {
    return authenticatedAction(function($auth) {
        $member = new Member(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $member->save();
        return makeResponse(
            data: [$member->apiGetInfo("ADMIN")],
            code: 200,
            message: "CREATED_MEMBER",
            message_long: "Member '{$member->getFullName()}' created.",
            num_items: 1,
            num_items_total: Member::getObjectsCount(),
        );
    });
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $fields = getFieldsForApi($_GET);
        $member = new Member((int)$i_member);
        return makeResponse(
            data: [$member->apiGetInfo("ADMIN", $fields)],
            code: 200,
            num_items: 1,
            num_items_total: Member::getObjectsCount(),
        );
    }, $i_member);
}, 'GET');

Route::add('/api/member/([0-9]*)', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($i_member);
        $put = getPutData();
        $memberInfo = $put;
        unset($memberInfo['groups']);
        $groupsInfo = $put['groups'];
        $member->updateProperties($memberInfo);
        $member->save();
        return makeResponse(
            code: 200,
            message: "UPDATED_MEMBER",
            message_long: "Member '{$member->getFullName()}' updated.",
        );
    }, $i_member);
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($i_member);
        $member->delete();
        return makeResponse(
            code: 200,
            message: "DELETED_MEMBER",
            message_long: "Member '{$member->getFullName()}' deleted.",
        );
    }, $i_member);
}, 'DELETE');

// group management
Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($id = $i_member);
        return makeResponse(
            data: $member->getGroups(),
            code: 200,
            num_items: count($member->getGroups()),
            num_items_total: count($member->getGroups()),
        );
    }, $i_member);
}, 'GET');

Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($id = $i_member);
        $member->setGroups(getPutData());
        return makeResponse(
            code: 200,
            message: "UPDATED_MEMBER_GROUPS",
            message_long: "Member '{$member->getFullName()}' groups updated.",
        );
    }, $i_member);
}, 'PUT');