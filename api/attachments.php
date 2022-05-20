<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/attachment/([0-9a-f_]*)', function($attachment_file) {
    return authenticatedAction(function($auth, $attachment_file) {
        $db = new DB();
        $data = $db->queryRow("SELECT * FROM attachment WHERE s_filename = ?", [$attachment_file]);
        header('Content-type: ' . $data['s_contenttype']);
        header('Content-Disposition: inline; filename="' . $data['s_name'] . '"');
        return readfile(ATTACHMENT_PATH . $attachment_file);
    }, $attachment_file);
}, 'GET');