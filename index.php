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

define("STREAMS", 1);

session_start();
$sessid = session_id();

ob_start("ob_gzhandler");

require_once("lib/Config.php");
require_once("lib/ws-php-library.php");
require_once("lib/stopwords.php");
require_once("lib/getid3/getid3/getid3.php");
require_once("lib/streams.lib.php");

$cfg = Config::getInstance();

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
    $mobileCss = apply_template("{$cfg->streamsRootDir}/tmpl/mobile-css.tmpl", array());
}

require_once("lib/auth.php");

$currentPlaylist = null;
if (file_exists($auth->currentPlaylist) && file_exists($auth->currentPlaylistDir)) {
    $currentPlaylist = file_get_contents($auth->currentPlaylist);
    $currentPlaylistDir = file_get_contents($auth->currentPlaylistDir);
}

if ($cfg->logging) {
    file_put_contents($cfg->logfile, date("Y-m-d H:i:s") . " ::: " . $_SERVER['REMOTE_ADDR'] . " ::: " 
            . $_SERVER['HTTP_USER_AGENT'] . " ::: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
}

if (isset($_SESSION['u']) && strlen($_SESSION['u']) > 0) {
    $sessid = $_SESSION['u'];
}

require_once("lib/actions.php");

$pageContent .= openTheDir($_GET['dir']);

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$contentPlayer = null;
if (isset($currentPlaylist) && strlen($currentPlaylist) > 0) {
    $esc_dir = preg_replace("/\\\"/", "\"", $currentPlaylistDir);
    $esc_dir = preg_replace("/\"/", "\\\"", $esc_dir);
    $html_dir = buildPlayerAlbumTitle($currentPlaylistDir);
    $contentPlayer = buildPlayerHtml($currentPlaylist, $currentPlaylistDir, 'false');
}

$a_indextmpl = array("viewport" => $viewport, "pageContent" => $pageContent, "message" => $message, 
        "jsMobileVar" => $jsMobileVar, "mobileCss" => $mobileCss, "content-player"=>$contentPlayer);
$html = apply_template("{$cfg->streamsRootDir}/tmpl/index.tmpl", $a_indextmpl);

/**
 * Return page
 */
ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();
