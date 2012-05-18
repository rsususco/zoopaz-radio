<?php
require_once("WsPhpLibrary.php");

$streamName = $_SERVER['argv'][1];

if ($streamName != "") {
    $streamName = preg_replace("/[^a-zA-Z0-9-_]/", "", $streamName);
    if ($streamName == "") {
        die("Stream name is empty after replacing [^a-zA-Z0-9-_] with nothing.\n");
    }
} else {
    die("Stream name is empty.\n");
}

exec("find ../music/AllMaciTunes/Armin\ van\ Buuren/Imagine\ the\ Remixes/ -name '*.mp3' > ArminVanBuuren.m3u.tmp");
file_put_contents("ArminVanBuuren.m3u", "");
$a_file = file("ArminVanBuuren.m3u.tmp");
foreach ($a_file as $k=>$file) {
    $file = rtrim($file, "\n");
    $file = preg_replace("/^\.\.\//", "", $file);
    $file = "/var/www/nas/music/htdocs/" . $file;
    $enc_file = urlencode($file);
    file_put_contents("ArminVanBuuren.m3u", "http://music.example.com/stream/play.php?file=" . $enc_file . "\n", FILE_APPEND);
}
unlink("ArminVanBuuren.m3u.tmp");
