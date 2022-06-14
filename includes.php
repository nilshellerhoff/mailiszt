<?php

// if config file does not exist, use the sample file
if (!file_exists('config.php')) {
    copy('config.php.sample', 'config.php');
}

require_once('config.php');
require_once('functions.php');

require_once('classes/Attachment.php');
require_once('classes/DB.php');
require_once('classes/Group.php');
require_once('classes/Mail.php');
require_once('classes/Mailbox.php');
require_once('classes/Member.php');
require_once('classes/User.php');
require_once('classes/Setting.php');
require_once('classes/Logger.php');
require_once('classes/Util.php');

require_once('vendor/autoload.php');