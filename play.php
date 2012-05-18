<?php
function getWebRoot() {
    $a_webconfig = file("../../../../../config/config.txt");
    if ( is_array($a_webconfig) && count($a_webconfig) ) {
        foreach ( $a_webconfig as $k=>$line ) {
            if ( preg_match("/^webroot=(.+)$/i", $line, $a) ) {
                return trim($a[1]);
            }
        }
    }
    die("Sorry but I could not find your web root.");
}

$webroot = getWebRoot();
if (file_exists("{$webroot}/wjsams.com/htdocs/music.wjsams.com/htdocs/music/" . $_GET['file']) && preg_match("/\.mp3$/i", $_GET['file'])) {
    readfile("{$webroot}/wjsams.com/htdocs/music.wjsams.com/htdocs/music/" . $_GET['file']);
    die();
}
