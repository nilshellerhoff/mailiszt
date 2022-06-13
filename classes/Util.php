<?php

class Util {
    /** Format a string with placeholders
     * @param $template string template to format where {{field}} is replaced by the value of the field
     * @param $fields array fields to replace in the template
     * @return string formatted template
     */
    public static function formatTemplate($template, $fields) {
        foreach ($fields as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }
        return $template;
    }

}