<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once('includes.php');

// check authentication
$authenticated = false;
$failedLogin = false;

// check whether a valid auth token is passed as post parameter and if not show login form
if (isset($_COOKIE["auth"])) {
    $token = $_COOKIE["auth"];
    $authenticated = User::checkAuthentication($token);
}

// check if valid user and password are passed in POST, if yes authenticate
if (!$authenticated) {
    if (isset($_POST["user"]) && isset($_POST["password"])) {
        $token = User::authenticate($_POST["user"], $_POST["password"]);
        if ($token) {
            setcookie("auth", $token, ["expires" => time() + 60 * 60, "path" => "/", "samesite" => "Lax"]);
            $authenticated = true;
        } else {
            $failedLogin = true;
        }
    }
}

// if still not authenticated, show login form
if (!$authenticated) {
    echo<<<HTML
    <form action="{$_SERVER['REQUEST_URI']}" method="post">
        <input type="text" name="user" placeholder="Username" />
        <br>
        <input type="password" name="password" placeholder="Password" />
        <br>
        <input type="submit" value="Login" />
    </form>
    HTML;

    if ($failedLogin) {
        echo "<p style='color: red;'>Login failed</p>";
    }
    
    die();
}

$db = new DB();

// reset DB
if (isset($_GET["resetDB"]) && $_GET["resetDB"] == 'true') {
    rename(DB_FILE, DB_FILE . '_bkp_' . date(DATE_FORMAT));
}

// upgrade DB
if (isset($_GET["upgradeDB"]) && $_GET["upgradeDB"] == 'true') {
    $db->upgradeDB();
}

$query = $_GET["query"] ?? NULL;
$results = [];

$results = [];

$queries = explode(';', str_replace("\r\n\r\n", ";", $query));

foreach($queries as $q) {
    if (trim($q) != "") {
        try {
            $ret = $db->queryAll($q);
            if ($ret) {
                $results[] = [
                    "rows"    => $ret,
                    "headers" => array_keys($ret[0])
                ];
            } else {
                $results[] = [
                    "error" => $db->lastErrorMsg()
                ];
            }
        } catch (Throwable $e) {
            $error_msg = $db->lastErrorMsg();
            if ($error_msg != "not an error") {
                $results[] = [
                    "error" => $error_msg
                ];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <style>
        <?php include('index.css') ?>
    </style>
</head>
<body>
    <div class=textarea>
        <form action="" method="get" id="query">
            <textarea id=codearea autofocus onkeydown="pressed(event)" name="query" form="query" rows=20 style="width: 100%"><?= $query ?></textarea>
            <input type="submit" value="Run it!">
            <a href="/dbadmin?resetDB=true" onclick="return confirm('reset DB?')" style="float: right">Reset DB</a>
            <a href="/dbadmin?upgradeDB=true" onclick="return confirm('upgrade DB?')" style="float: right; margin: 0px 10px">Upgrade DB</a>
        </form>
    </div>

    <div class=resultsarea>
    <br>

    <?php foreach($results as $result): ?>
        <?php if(isset($result["error"])): ?>
            <span class="error"><?= $result["error"] ?></span><br>
        <?php else: ?>
        <table>
            <tr>
                <?php foreach ($result["headers"] as $header): ?>
                    <th><?= $header ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($result["rows"] as $row): ?>
                <tr>
                    <?php foreach ($row as $column => $value): ?>
                        <td title="<?= htmlentities($value) ?>"><?= htmlentities($value) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <script>
        <?php include('index.js') ?>
    </script>
</body>
</html>