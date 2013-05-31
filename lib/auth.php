<?php
/**
 * Configuration
 */
require_once("lib/Auth.php");

if (!isset($_SESSION['auth'])) {
    $auth = new Auth();
} else {
    $auth = unserialize($_SESSION['auth']);
}

if ($auth->tries > $auth->maxTries) {
    $auth->message = "<strong>Locked out.</strong><br />";
    $auth->disabled = "disabled='disabled'";
}

if ($_GET['action'] == "login" && $auth->tries > $auth->maxTries) {
    $_SESSION['auth'] = serialize($auth);
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

if ($_GET['action'] == "login") {
    if (array_key_exists($_POST['username'], $auth->users)) {
        if ($_POST['password'] == $auth->users[$_POST['username']]) {
            // Set session login variables.
            if (!file_exists("sessions/users")) {
                mkdir("sessions/users");
            }
            $auth->userDir = "sessions/users/{$_POST['username']}";
            if (!file_exists($auth->userDir)) {
                mkdir($auth->userDir);
            }
            $auth->username = $_POST['username'];
            $auth->is_logged_in = true;
            $auth->tries = 0;
            $auth->currentPlaylist = $auth->userDir . "/currentPlaylist.obj";
            $auth->currentPlaylistDir = $auth->userDir . "/currentPlaylistDir.obj";
            $_SESSION['auth'] = serialize($auth);
            header("Location:{$_SERVER['PHP_SELF']}");
            exit();
        } else {
            $auth->tries = $auth->tries + 1;
        }
    } else {
        $auth->tries = $auth->tries + 1;
    }
}

if (!$auth->is_logged_in || $auth->tries > $auth->maxTries) {
    $_SESSION['auth'] = serialize($auth);
    print($auth->message);
    print <<<eof
<form action="{$_SERVER['PHP_SELF']}?action=login" method="post">
username: <input {$auth->disabled} type="text" name="username" id="username" /><br />
password: <input {$auth->disabled} type="password" name="password" id="password" /><br />
<input {$auth->disabled} type="submit" value="login" />
</form>
eof;
    die();
}
