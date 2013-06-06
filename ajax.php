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

require_once("lib/ws-php-library.php");
require_once("lib/getid3/getid3/getid3.php");
require_once("lib/streams.lib.php");
require_once("lib/auth.php");
require_once("lib/Config.php");

$cfg = Config::getInstance();

if ($_GET['action'] == "createPlaylistJs") {
    $html = createPlaylistJs($_GET['dir']);

    ob_start();
    ob_implicit_flush(0);
    print($html);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "openDir") {
    $pageContent = openTheDir($_GET['dir']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "search") {
    $pageContent = search($_GET['q']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "addToPlaylist") {
    $pageContent = addToPlaylist($_GET['dir']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "addToPlaylistFile") {
    $pageContent = addToPlaylistFile($_GET['dir'], $_GET['file']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "getRandomPlaylist") {
    $pageContent = getRandomPlaylistJson($_GET['num']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "playRadio") {
    $html = playRadio($_GET['num']);

    ob_start();
    ob_implicit_flush(0);
    print($html);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "logout") {
    logout();
    die();
} else if ($_GET['action'] == "getAlbumArt") {
    $id3 = id3($_GET['dir'], $_GET['file']);
    $html = json_encode(array("albumart"=>$id3['albumart']));

    ob_start();
    ob_implicit_flush(0);
    print($html);
    print_gzipped_page();
    die();
}
