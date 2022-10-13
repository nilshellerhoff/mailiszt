<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/member', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $members = Member::getAll((int)$_GET["limit"], (int)$_GET["offset"]);
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $members);
        return makeResponse($apiInfo);
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
        return makeResponse($member->apiGetInfo("ADMIN"));
    });
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $fields = getFieldsForApi($_GET);
        $member = new Member((int)$i_member);
        return makeResponse($member->apiGetInfo("ADMIN", $fields));
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
    }, $i_member);
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($i_member);
        $member->delete();
    }, $i_member);
}, 'DELETE');

// group management
Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($id = $i_member);
        return makeResponse($member->getGroups());
    }, $i_member);
}, 'GET');

Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    return authenticatedAction(function($auth, $i_member) {
        $member = new Member($id = $i_member);
        $member->setGroups(getPutData());
    }, $i_member);
}, 'PUT');