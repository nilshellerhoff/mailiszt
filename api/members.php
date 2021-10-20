<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/member', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $db = new DB();
        $member_ids = $db->queryColumn("SELECT i_member FROM member");
        $members = [];
        foreach ($member_ids as $id) {
            $member = new Member($id = $id);
            $members[] = $member->apiGetInfo("ADMIN");
        }
        return makeResponse($members);
    }
}, 'GET');

Route::add('/api/member/add', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $member->save();
        return makeResponse($member->apiGetInfo("ADMIN"));
    }
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member((int)$i_member);
        return makeResponse($member->apiGetInfo("ADMIN"));
    }
}, 'GET');

Route::add('/api/member/([0-9]*)', function($i_member) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member($i_member);
        $put = getPutData();
        $memberInfo = $put;
        unset($memberInfo['groups']);
        $groupsInfo = $put['groups'];
        $member->updateProperties($memberInfo);
        $member->save();
    }
}, 'PUT');

Route::add('/api/member/([0-9]*)', function($i_member) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member($i_member);
        $member->delete();
    }
}, 'DELETE');

// group management
Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member($id = $i_member);
        return makeResponse($member->getGroups());
    }
}, 'GET');

Route::add('/api/member/([0-9]*)/groups', function($i_member) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $member = new Member($id = $i_member);
        $member->setGroups(getPutData());
    }
}, 'PUT');