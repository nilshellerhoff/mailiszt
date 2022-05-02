<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/member', function() {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $fields = array_filter(explode(",", $_GET['fields']));
        $members = Member::getAll();
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $members);
        return makeResponse($apiInfo);
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
        $fields = array_filter(explode(",", $_GET['fields']));
        $member = new Member((int)$i_member);
        return makeResponse($member->apiGetInfo("ADMIN", $fields));
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