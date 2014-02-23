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

//require_once("lib/Config.php");
require_once("lib/WsTmpl.php");
require_once("lib/getid3/getid3/getid3.php");
require_once("lib/StreamsSearchIndexer.php");
require_once("lib/Streams.php");
require_once("lib/Auth.php");

if (!isset($_SESSION['auth'])) {
    $auth = new Auth();
} else {
    $auth = unserialize($_SESSION['auth']);
}

//$cfg = Config::getInstance();
$t = new WsTmpl();
$streams = new Streams($cfg, $auth, $t);

if (!$auth->is_logged_in) {
    print(json_encode(array("is_logged_in" => false)));
    die();
}

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
} else if ($_GET['action'] == "getHomeNavigation") {
    print($streams->getHomeNavigation());
    $streams->print_gzipped_page();
    die();
} else if ($_GET['action'] == "openMyRadio") {
    print($streams->openMyRadio());
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
    if (isset($_GET['station']) && $_GET['station'] != null && $_GET['station'] != "undefined") {
        $o = $streams->getRandomPlaylistJson($_GET['num'], "{$auth->userDir}/stations/{$_GET['station']}.files.db");
    } else if (isset($_GET['personal']) && $_GET['personal'] == "yes") {
        $o = $streams->getRandomPlaylistJson($_GET['num'], "{$auth->userDir}/{$cfg->personalRadioDatabase}");
    } else {
        $o = $streams->getRandomPlaylistJson($_GET['num']);
    }
    print($o);
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
} else if ($_GET['action'] == "getHomeIndex") {
    print($streams->getHomeIndex());
    die();
} else if ($_GET['action'] == "createPersonalRadio") {
    print($streams->createPersonalRadio($_GET['dir'], $_GET['num']));
    die();
} else if ($_GET['action'] == "startPersonalRadio") {
    print($streams->startPersonalRadio($_GET['num'], $_GET['station']));
    die();
} else if ($_GET['action'] == "addToPersonalRadio") {
    print($streams->addToPersonalRadio($_GET['dir']));
    die();
} else if ($_GET['action'] == "removeFromPersonalRadio") {
    print($streams->removeFromPersonalRadio($_GET['dir'], $_GET['station']));
    die();
} else if ($_GET['action'] == "saveMyRadio") {
    print($streams->saveMyRadio($_GET['name']));
    die();
} else if ($_GET['action'] == "viewMyRadio") {
    print($streams->viewMyRadio());
    die();
} else if ($_GET['action'] == "loadStation") {
    print($streams->loadStation($_GET['station']));
    die();
} else if ($_GET['action'] == "removeRadioStation") {
    print($streams->removeRadioStation($_GET['station']));
} else if ($_GET['action'] == "suggest") {
    print($streams->suggestRadioStationItem($_GET['term']));
} else if ($_GET['action'] == "addToRadioStation") {
    print($streams->addToRadioStation($_GET['station'], $_GET['dir']));
} else {
    die("Unused action.");
}
