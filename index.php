<?php

/*
Copyright 2014 Weldon Sams

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

if (!file_exists("lib/Config.php") || !file_exists("lib/Auth.php")) {
    header("Location:installer.php");
    exit();
}
require_once("lib/Config.php");
$cfg = Config::getInstance();

if (!file_exists($cfg->alternateSessionDir)) {
    $cfg->mkdirRecursive($cfg->alternateSessionDir);
}
session_save_path($cfg->alternateSessionDir);

session_start();
$sessid = session_id();

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once("lib/WsTmpl.php");
require_once("lib/getid3/getid3/getid3.php");
require_once("lib/StreamsSearchIndexer.php");
require_once("lib/Streams.php");
require_once("lib/Auth.php");

function getAuth() {
    if (!isset($_SESSION['auth'])) {
        $auth = new Auth();
    } else {
        $auth = unserialize($_SESSION['auth']);
    }
    return $auth;
}
$auth = getAuth();
if (!isset($auth->maxTries)) {
    unset($_SESSION['auth']);
    $auth = getAuth();
}

$t = new WsTmpl();
$streams = new Streams($cfg, $auth, $t);

if ($cfg->logging) {
    file_put_contents($cfg->logfile, date("Y-m-d H:i:s") . " ::: " . $_SERVER['REMOTE_ADDR'] . " ::: " 
            . $_SERVER['HTTP_USER_AGENT'] . " ::: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
}

$viewport = "";
// Current the styles do not look well on phones
// Coming soon.
$isMobile = false;
$jsMobileVar = "isMobile = false;";
$mobileCss = "";
if (preg_match("/(Android|iPhone|Phone|iPad|Nexus)/i", $_SERVER['HTTP_USER_AGENT'])) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
    $isMobile = true;
    $jsMobileVar = "isMobile = true;";
    $t->setData(array());
    $t->setFile("{$cfg->streamsRootDir}/tmpl/mobile-css.tmpl");
    $mobileCss = $t->compile();
}

require_once("lib/auth.php");

$currentPlaylist = null;
if (file_exists($auth->currentPlaylist) && file_exists($auth->currentPlaylistDir)) {
    $currentPlaylist = file_get_contents($auth->currentPlaylist);
    $currentPlaylistDir = file_get_contents($auth->currentPlaylistDir);
}

if (isset($_SESSION['u']) && strlen($_SESSION['u']) > 0) {
    $sessid = $_SESSION['u'];
}

require_once("lib/actions.php");

// This must come after lib/actions.php in order to send album downloads properly.
ob_start("ob_gzhandler");

$pageContent .= $streams->openTheDir($_GET['dir']);

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$contentPlayer = null;
if (isset($currentPlaylist) && strlen($currentPlaylist) > 0) {
    $esc_dir = preg_replace("/\\\"/", "\"", $currentPlaylistDir);
    $esc_dir = preg_replace("/\"/", "\\\"", $esc_dir);
    $html_dir = $streams->buildPlayerAlbumTitle($currentPlaylistDir);
    $contentPlayer = $streams->buildPlayerHtml($currentPlaylist, $currentPlaylistDir, 'false');
}

$t->setData(array("viewport" => $viewport, "pageContent" => $pageContent, 
        "message" => $message, "jsMobileVar" => $jsMobileVar, 
        "mobileCss" => $mobileCss, "content-player"=>$contentPlayer));
$t->setFile("{$cfg->streamsRootDir}/tmpl/index.tmpl");
$html = $t->compile();

/**
 * Return page
 */
ob_start();
ob_implicit_flush(0);
print($html);
$streams->print_gzipped_page();
