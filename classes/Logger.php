<?php

class Logger {
    public static function log($message, $level = 'INFO') {
        $db = new DB();

        // if we are logging a debug message and DEBUG is not enabled do nothing
        if ($level == 'DEBUG' && !DEBUG) {
            return;
        }

        $db->insert(
            'log',
            [
                's_level' => $level,
                's_message' => $message
            ]
        );
    }
}