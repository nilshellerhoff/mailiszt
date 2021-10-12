<?php

require_once('includes.php');

// Base dir
define('BASE_DIR', __DIR__ . '/');

// DB file (SQLite)
define('DB_FILE', __DIR__ . '/mailiszt.db');

// path where attachments are saved
define('ATTACHMENT_PATH', __DIR__ . '/data/attachments/');

// what to do with mails when processed
define('MAIL_PROCESSED_ACTION', 'nada');
define('MAIL_PROCESSED_FOLDER', 'INBOX/mailiszt_processed');

// php-imap communication sequence type
define('IMAP_SEQUENCE_TYPE', 'ST_UID');

// IMAP folder which contains new mails
define('IMAP_PENDING_FOLDER', 'INBOX');

// how long are authentication tokens valid in seconds
define('AUTH_TOKEN_LIFETIME', 3600);

// date format used in PHP for DB operations
define('DATE_FORMAT', "Y/m/d H:i:s");