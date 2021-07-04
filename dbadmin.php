<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('includes.php');

$query = $_GET["query"] ?? NULL;
$results = [];

$db = new DB();

if (isset($query)) {
    $results = $db->query($query);
    $results = $db->fetchAll($results);

    $headers = array_keys($results[0]);
}


?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .textarea {width: 100%; max-width: 1200px; height: 350px;}
        .resultsarea {height: calc(100vh - 370px); overflow: auto; white-space:nowrap;}
        table {margin: 5px 0px; font-size: 11pt;}
        table, td, th {border: 1px solid black; border-collapse: collapse;}
        th {padding: 4px    ; background-color: #ddd;}
        td {padding: 4px; max-width: 500px; overflow: hidden; text-overflow: ellipsis}
        tr:nth-child(2n+1) {background: #f5f5f5;}
    </style>
</head>
<body>
    <div class=textarea>
        <form action="dbadmin.php" method="get" id="query">
            <textarea id=codearea autofocus onkeydown="pressed(event)" name="query" form="query" rows=20 style="width: 100%"><?= $query ?></textarea>
            <input type="submit" value="Run it!">
            <a href="dbadmin.php?resetDB=true" onclick="return confirm('reset DB?')" style="float: right">Reset DB</a>
        </form>
    </div>

    <div class=resultsarea>
    <br>
    <table>
        <tr>
            <?php foreach ($headers as $header): ?>
                <th><?= $header ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($results as $result): ?>
            <tr>
                <?php foreach ($result as $key => $value): ?>
                    <td title="<?= htmlentities($value) ?>"><?= htmlentities($value) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <script>
// keycodes with ctrl key
var keycodes = {
    13: submitform, // ctrl + enter
    163: comment // ctrl + #
} // ctrl + y

function pressed(e) {
    let keycode = window.event ? event.keyCode : e.which
    if (e.ctrlKey) {
        if (keycodes[keycode] !== undefined) {
            keycodes[keycode]();
        }
    }
}

function submitform() {
    document.forms[0].submit();
}

function comment(selector = '#codearea') {
    // get the current selection
    let selstart = document.querySelector(selector).selectionStart;
    let selend = document.querySelector(selector).selectionEnd;

    let comment_lines = getLinesForComment(selector, selstart, selend);
    let txtValue = document.querySelector(selector).value.split("\n");
    let newTxtVal = txtValue.slice(comment_lines[0], comment_lines[1]+1)

    // loop through lines and toggle comment for each
    newTxtVal.forEach(function(line, index) {
        newTxtVal[index] = toggleComment(line)
    });

    // join all the lines to one array
    let newLines = txtValue.slice(0, comment_lines[0])
        .concat(newTxtVal)
        .concat(txtValue.slice(comment_lines[1]+1));

    // comment the values
    document.querySelector(selector).value = newLines.join("\n");
}

function toggleComment(line) {
    // if line starts with "--", remove it, else put it
    // if it is empty do nothing
    if (line.match(/^$/)) {
        return line
    } else if (line.match(/^-- /)) {
        return line.substring(3);
    } else {
        return "-- " + line;
    }
}

function getLinesForComment(selector, selstart, selend) {
    let txt = document.querySelector(selector).value;
    
    let comment_start = lineFromIndex(txt, selstart);
    let comment_end = lineFromIndex(txt, selend);

    return [comment_start, comment_end]
}

function lineFromIndex(txt, idx) {
    // line where index is found is equal to the number of '\n' before the index
    let txtbefore = txt.substring(0, idx);
    return (txtbefore.match(/\n/g) || []).length;
}
    </script>
</body>
</html>