<?php 

include('includes.php');

use Steampixel\Route;

Route::add('/api/mailbox', function() {
    return authenticatedAction(function($auth) {
        $fields = getFieldsForApi($_GET);
        $mailboxes = Mailbox::getAll(...getLimitForApi($_GET));
        $apiInfo = array_map(fn($m) => $m->apiGetInfo($auth["s_role"], $fields), $mailboxes);
        return makeResponse(
            data: $apiInfo,
            code: 200,
            num_items: count($apiInfo),
            num_items_total: Mailbox::getObjectsCount(),
        );
    });
}, 'GET');

Route::add('/api/mailbox/add', function() {
    return authenticatedAction(function($auth) {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        $mailbox->save();
        $response_data = [$mailbox->apiGetInfo("ADMIN")];
        return makeResponse(
            data: $response_data,
            code: 200,
            num_items: count($response_data),
            num_items_total: Mailbox::getObjectsCount(),
        );
    });
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $fields = getFieldsForApi($_GET);
        $mailbox = new Mailbox((int)$i_mailbox);
        $response_data = [$mailbox->apiGetInfo("ADMIN", $fields)];
        return makeResponse(
            data: $response_data,
            code: 200,
            num_items: count($response_data),
            num_items_total: Mailbox::getObjectsCount(),
        );
    }, $i_mailbox);
}, 'GET');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->updateProperties(getPutData());
        $mailbox->save();
        return makeResponse(
            code: 200,
            message: "UPDATED_MAILBOX",
            message_long: "returned mailbox '{$mailbox->properties['s_name']}'",
        );
    }, $i_mailbox);
}, 'PUT');

Route::add('/api/mailbox/([0-9]*)', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->delete();
        return makeResponse(
            code: 200,
            message: "DELETED_MAILBOX",
            message_long: "deleted mailbox '{$mailbox->properties['s_name']}'",
        );
    }, $i_mailbox);
}, 'DELETE');

// groups management
Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $response_data = $mailbox->getGroups();
        return makeResponse(
            data: $response_data,
            code: 200,
            num_items: count($response_data),
        );
    }, $i_mailbox);
}, 'GET');

Route::add('/api/mailbox/([0-9]*)/groups', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        $mailbox->setGroups(getPutData());
        $mailbox->save();
        return makeResponse(
            code: 200,
            message: "UPDATED_MAILBOX_GROUPS",
            message_long: "updated groups of mailbox '{$mailbox->properties['s_name']}'",
        );
    }, $i_mailbox);
}, 'PUT');

// getting recipients of mailbox
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox($i_mailbox);
        return makeResponse(
            data: $mailbox->getRecipients(),
            code: 200,
        );
    }, $i_mailbox);
}, 'GET');

// getting recipients of mailbox with custom groups
Route::add('/api/mailbox/([0-9]*)/recipients', function($i_mailbox) {
    return authenticatedAction(function($auth, $i_mailbox) {
        $mailbox = new Mailbox(
            $id = null,
            $obj = null,
            $properties = getPutData()
        );
        return makeResponse(
            data: $mailbox->getRecipients(),
            code: 200,
        );
    }, $i_mailbox);
}, 'PUT');

// actions for forwarding mails
Route::add('/api/mailbox/forward', function() {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::info("Email fetching is disabled", 'EMAIL_FETCHING_DISABLED');
        return;
    } else {
        $mailboxes = Mailbox::getAll();
        $mail_cnt = 0;
        $mailbox_cnt = 0;

        foreach ($mailboxes as $mailbox) {
            try {
                $mails = $mailbox->fetchMails();
                foreach ($mails as $mail) {
                    try {
                        $mail->save();
                        $mail->forwardMail($mailbox);
                        $mail_cnt++;        
                    } catch (\Throwable $e) {
                        Logger::warning("could not forward mail i_mail = {$mail->properties['i_mail']}. Error: {$e->getMessage()}", "MAIL_FORWARDING_FAILED");
                    }
                }
                $mailbox_cnt++;
            } catch (\Throwable $e) {
                Logger::warning("could not check mailbox i_mailbox = {$mailbox->properties['i_mailbox']}. Error: {$e->getMessage()}", "MAILBOX_CHECK_FAILED");
            }
        }

        Logger::debug("Checked {$mailbox_cnt} mailboxes, found $mail_cnt mails to forward", 'MAILBOX_CHECK_SUCCESS');
    }
});

Route::add('/api/mailbox/([0-9]*)/forward', function($i_mailbox) {
    if (!Setting::getValue('enable_email_fetching')) {
        Logger::info("Email fetching is disabled", 'EMAIL_FETCHING_DISABLED');
    } else {
        $mail_cnt = 0;
        $mailbox = new Mailbox($id = $i_mailbox);

        try {   
            $mails = $mailbox->fetchMails();
            foreach ($mails as $mail) {
                try {
                    $mail->save();
                    $mail->forwardMail($mailbox);
                    $mail_cnt++;        
                } catch (\Throwable $e) {
                    Logger::warning("could not forward mail i_mail = {$mail->properties['i_mail']}. Error: {$e->getMessage()}", "MAIL_FORWARDING_FAILED");
                }
            }
            Logger::debug("Checked mailbox i_mailbox = {$mailbox->properties['i_mailbox']}, found $mail_cnt mails to forward", 'MAILBOX_CHECK_SUCCESS');

        } catch (\Throwable $e) {
            Logger::warning("could not check mailbox i_mailbox = {$mailbox->properties['i_mailbox']}. Error: {$e->getMessage()}", "MAILBOX_CHECK_FAILED");
        }
    }
});