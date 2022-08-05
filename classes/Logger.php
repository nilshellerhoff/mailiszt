<?php

class Logger extends Base {
    public static $table = "log";
    public static $identifier = "i_log";

    public $exposedInfo = [
        "ADMIN"     => ["i_log", "s_level", "s_eventtype", "s_message", "d_inserted", "d_updated"],
    ];

    public static function log($message, $eventtype = '', $level = 'INFO') {
        $db = new DB();

        // if we are logging a debug message and DEBUG is not enabled do nothing
        if ($level == 'DEBUG' && !DEBUG) {
            return;
        }

        $db->insert(
            'log',
            [
                's_level' => $level,
                's_eventtype' => $eventtype,
                's_message' => $message
            ]
        );
    }

    public static function debug($message) {
        self::log($message, 'DEBUG');
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function warning($message) {
        self::log($message, 'WARNING');
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
}