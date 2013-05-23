<?php

exec("find . -type d > dir.list");
$a_list = file("dir.list");

$f = "findMissingAlbumArt.log";

file_put_contents($f, "");

foreach ($a_list as $k=>$v) {
    $v = trim($v);
    if (!file_exists("{$v}/cover.jpg") && !file_exists("{$v}/montage.jpg")) {
        $w = preg_replace("/ /", "\ ", $v);

        file_put_contents($f, "{$v} does not have cover art.\n", FILE_APPEND);

        $a = glob($v . "/*.{jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG}", GLOB_BRACE);
        if (count($a) > 0) {

            foreach ($a as $i) {
                if (!preg_match("/montage/", $i)) {
                    file_put_contents($f, "{$i}, ", FILE_APPEND);    
                }
            }
        }

        file_put_contents($f, "\n\n", FILE_APPEND);
    }
}

unlink("dir.list");

