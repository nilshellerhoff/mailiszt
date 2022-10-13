<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/attachment/([0-9]*)(.*)', function($i_attachment, $name) {
    return authenticatedAction(function($auth, $i_attachment, $name) {
        $attachment = new Attachment($i_attachment);
        
        // if the name is not the attachment name, redirect
        if ($name != $attachment->properties['s_name'] && $name != '/' . $attachment->properties['s_name']) {
            header('Location: ' . '/api' . $attachment->getUrl());
            return;
        }

        header('Content-type: ' . $attachment->properties['s_contenttype']);
        header('Content-Disposition: inline;');
        return readfile(ATTACHMENT_PATH . $attachment->properties['s_filename']);
    }, $i_attachment, $name);
}, 'GET');