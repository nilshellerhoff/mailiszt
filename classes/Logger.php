<?php

class Logger {
    public static function log($message, $level = 'INFO') {
        $db = new DB();
        $db->insert(
            'log',
            [
                's_level' => $level,
                's_message' => $message
            ]
        );
    }
}