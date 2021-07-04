<?php

// globally defined functions
function pprint($var, $name=NULL) {
    // pretty print variables for debug
    if (isset($name)) {
        echo '<br>- ' . $name . '<br>';
    }
    echo '<pre style="background-color: #ddd">' . htmlentities(var_export($var, true)) . '</pre>';
}