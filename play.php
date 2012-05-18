<?php
function getWebRoot() {
    return "/var/www";
}

$webroot = getWebRoot();
if (file_exists("{$webroot}/music.example.com/htdocs/music/" . $_GET['file']) && preg_match("/\.mp3$/i", $_GET['file'])) {
    readfile("{$webroot}/music.example.com/htdocs/music/" . $_GET['file']);
    die();
}
