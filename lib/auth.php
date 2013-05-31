<?php
/**
 * Configuration
 */

$maxTrys = 5;

// Set your users here.
$users = array("wjsams@gmail.com"=>"ilovesiam", "nora.roggeveen@gmail.com"=>"pizers");

// start: login
$year = date("Y");
$month = date("m");
$day = date("d");
$hour = date("H");
$minute = date("i");
$seconds = date("s");

// Create session directories
if (!file_exists("sessions")) {
    mkdir("sessions");
}
if (!file_exists("sessions/logins")) {
    mkdir("sessions/logins");
}
if (!file_exists("sessions/logins/{$year}")) {
    mkdir("sessions/logins/{$year}");
}
if (!file_exists("sessions/logins/{$year}/{$month}")) {
    mkdir("sessions/logins/{$year}/{$month}");
}
if (!file_exists("sessions/logins/{$year}/{$month}/{$day}")) {
    mkdir("sessions/logins/{$year}/{$month}/{$day}");
}
$sessionDir = "sessions/logins/{$year}/{$month}/{$day}/{$sessid}";
if (!file_exists($sessionDir)) {
    mkdir($sessionDir);
}

// Session files.
$session = "{$sessionDir}/" . $_SERVER['REMOTE_ADDR'] . ".session";
$try = "{$sessionDir}/" . $_SERVER['REMOTE_ADDR'] . ".try";

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
            // Set session login variables.
            if (!file_exists("sessions/users")) {
                mkdir("sessions/users");
            }
            $userDir = "sessions/users/{$_POST['username']}";
            if (!file_exists($userDir)) {
                mkdir($userDir);
            }
            $_SESSION['is_logged_in'] = true;
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['sessionDir'] = $sessionDir;
            $_SESSION['userDir'] = $userDir;
            file_put_contents($session, "logged in as {$_POST['username']} on " . date("Y-m-d H:i:s"));
            file_put_contents($try, 0);
            header("Location:{$_SERVER['PHP_SELF']}");
            exit();
        } else {
            file_put_contents($try, $trys + 1);
        }
    } else {
        file_put_contents($try, $trys + 1);
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

