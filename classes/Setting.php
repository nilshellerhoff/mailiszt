<?php

class Setting {

    public static function getValue($name) {
        $db = new DB();
        $result = $db->queryRow(
            "SELECT s_type, v_value FROM setting WHERE s_name = ?",
            [$name]
        );
        return Setting::decodeValue($result["v_value"], $result["s_type"]);
    }

    public static function setValue($name, $value) {
        $db = new DB();
        $type = $db->queryScalar(
            "SELECT s_type FROM setting WHERE s_name = ?",
            [$name]
        );
        $db->update(
            "setting",
            ["v_value" => Setting::encodeValue($type, $value)],
            ["s_name" => $name]
        );
    }

    private static function decodeValue($value, $type) {
        if ($type == 'string') return strval($value);
        if ($type == 'int') return intval($value);
        if ($type == 'float') return floatval($value);
        if ($type == 'json') return json_decode($value);

    }
    private static function encodeValue($value, $type) {
        if ($type == 'string') return strval($value);
        if ($type == 'int') return intval($value);
        if ($type == 'float') return floatval($value);
        if ($type == 'json') return json_encode($value);
    }
}