<?php

// globally defined functions
function pprint($var, $name=NULL) {
    // pretty print variables for debug
    if (isset($name)) {
        echo '<br>- ' . $name . '<br>';
    }
    $code = htmlentities(print_r($var, true));

    echo <<<HTML
    <style>
        div {
            background-color: #ddd;
            padding: 8px;
            margin: 10px 0px;

        }
        pre {
            background-color: #ddd;
            /* background: repeating-linear-gradient(
                90deg,
                #bbb,
                #bbb 1px,
                #ddd 1px,
                #ddd 14px
            ); */
            background: repeating-linear-gradient(
                90deg,
                #bbb,
                #bbb 1px,
                #ddd 1px,
                #ddd 29px
            );
            padding: 0px; margin: 0px;
        }
    </style>
    <div><pre>$code</pre></div>
    HTML;
}

// calculate the difference between two dates in seconds
function dateDiffSeconds($date1 , $date2) {
    $datetime1 = date_create($date1);
    $datetime2 = date_create($date2);
   
    $interval = date_diff($datetime1, $datetime2);
   
    return $interval->format('%s');
}