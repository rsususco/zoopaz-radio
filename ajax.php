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

session_start();
$sessid = session_id();

require_once("lib/Config.php");
require_once("lib/WsTmpl.php");
require_once("lib/getid3/getid3/getid3.php");
require_once("lib/Streams.php");

require_once("lib/Auth.php");
if (!isset($_SESSION['auth'])) {
    $auth = new Auth();
} else {
    $auth = unserialize($_SESSION['auth']);
}

if (!$auth->is_logged_in) {
    print(json_encode(array("is_logged_in" => false)));
    die();
}

$cfg = Config::getInstance();
$t = new WsTmpl();
$streams = new Streams($cfg, $auth, $t);

ob_start();
ob_implicit_flush(0);

if ($_GET['action'] == "createPlaylistJs") {
    print($streams->createPlaylistJs($_GET['dir']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "openDir") {
    print($streams->openTheDir($_GET['dir']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "search") {
    print($streams->search($_GET['q']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "clearPlaylist") {
    print($streams->clearPlaylist());
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "addToPlaylist") {
    print($streams->addToPlaylist($_GET['dir']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "addToPlaylistFile") {
    print($streams->addToPlaylistFile($_GET['dir'], $_GET['file']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "getRandomPlaylist") {
    print($streams->getRandomPlaylistJson($_GET['num']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "playRadio") {
    print($streams->playRadio($_GET['num']));
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "logout") {
    print($streams->logout());
    die();
} else if ($_GET['action'] == "getAlbumArt") {
    $id3 = $streams->id3($_GET['dir'], $_GET['file']);
    print(json_encode(array("albumart"=>$id3['albumart'])));
    $streams->print_gzipped_page();
    die();
} else {
    die("Unused action.");
}
