<?php

/*
Copyright 2013 Weldon Sams

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

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
    $pageContent = <<<eof
{$auth->message}
<form action="{$_SERVER['PHP_SELF']}?action=login" method="post">
    <fieldset>
        <legend>Login</legend>
        <input {$auth->disabled} type="text" name="username" id="username" placeholder="Email..." /><br />
        <input {$auth->disabled} type="password" name="password" id="password" placeholder="Password..." /><br />
        <input {$auth->disabled} type="submit" value="login" class="button" />
    </fieldset>
</form>
eof;
    $a_indextmpl = array("viewport" => $viewport, "pageContent" => $pageContent, "message" => $message, "jsMobileVar" => $jsMobileVar, 
            "mobileCss" => $mobileCss);
    $html = apply_template("tmpl/index.tmpl", $a_indextmpl);
    print($html);
    die();
}
