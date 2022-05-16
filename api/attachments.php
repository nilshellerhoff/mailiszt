<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/attachment/([0-9a-f_]*)', function($attachment_file) {
    $auth = checkAuthToken();
    if (!$auth) {
        return makeResponse('invalid authentication', 403);
    } else {
        $db = new DB();
        $data = $db->queryRow("SELECT * FROM attachment WHERE s_filename = ?", [$attachment_file]);
        header('Content-type: ' . $data['s_contenttype']);
        header('Content-Disposition: attachment; filename="' . $data['s_name'] . '"');
        return readfile(ATTACHMENT_PATH . $attachment_file);
    }
}, 'GET');