<?php
/**
 * Configuration
 */

$maxTrys = 5;

// Set your users here.
$users = array("wsams"=>"ilovesiam", "nroggeve"=>"pizers", "brsams"=>"r34g34equ", "anthony"=>"vitacco", "asik"=>"pradhan");

// start: login
if (!file_exists("sessions")) {
    mkdir("sessions");
}
$session = "sessions/" . $_SERVER['REMOTE_ADDR'] . ".txt";
$try = "sessions/" . $_SERVER['REMOTE_ADDR'] . ".try";
// clean up $_POST
foreach ($_POST as $k=>$v) {
    $k = preg_replace("/[^0-9a-zA-Z]/", "", $k);
    $v = preg_replace("/[^0-9a-zA-Z]/", "", $v);
    $_POST[$k] = $v;
}

if (!file_exists($try)) {
    file_put_contents($try, 0);
    $trys = 0;
} else {
    $trys = intval(file_get_contents($try));
}
$disabled = "";
$message = "";
if ($trys > $maxTrys) {
    $message = "<strong>Locked out.</strong><br />";
    $disabled = "disabled='disabled'";
}
if ($_GET['action'] == "login" && $trys > $maxTrys) {
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

if ($_GET['action'] == "login") {
    if (array_key_exists($_POST['username'], $users)) {
        if ($_POST['password'] == $users[$_POST['username']]) {
            file_put_contents($session, "logged in as {$_POST['username']} on " . date("Y-m-d H:i:s"));
            file_put_contents($try, 0);
            header("Location:{$_SERVER['PHP_SELF']}");
            exit();
        } else {
            file_put_contents($try, $trys+1);
        }
    } else {
        file_put_contents($try, $trys+1);
    }
}

if (!file_exists($session) || $trys > $maxTrys) {
    print($message);
    print <<<eof
<form action="{$_SERVER['PHP_SELF']}?action=login" method="post">
username: <input {$disabled} type="text" name="username" id="username" /><br />
password: <input {$disabled} type="password" name="password" id="password" /><br />
<input {$disabled} type="submit" value="login" />
</form>
eof;
    die();
}
// end: login

