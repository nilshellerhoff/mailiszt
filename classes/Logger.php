<?php

class Logger extends Base {
    public static $table = "log";
    public static $identifier = "i_log";

    public $exposedInfo = [
        "ADMIN"     => ["i_log", "s_level", "s_eventtype", "s_message", "d_inserted", "d_updated"],
    ];

    public static function log($message, $level = 'INFO', $eventtype = '') {
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

    public static function debug($message, $eventtype = '') {
        self::log($message, 'DEBUG', $eventtype);
    }

    public static function info($message, $eventtype = '') {
        self::log($message, 'INFO', $eventtype);
    }

    public static function warning($message, $eventtype = '') {
        self::log($message, 'WARNING', $eventtype);
    }

    public static function error($message, $eventtype = '') {
        self::log($message, 'ERROR', $eventtype);
    }
}