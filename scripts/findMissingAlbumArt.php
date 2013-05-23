<?php

exec("find . -type d > dir.list");
$a_list = file("dir.list");

file_put_contents("findMissingCoverArt.log", "");

foreach ($a_list as $k=>$v) {
    $v = preg_replace("/(\r|\n)/", "", $v);
    if (!file_exists("{$v}/cover.jpg")) {
        ob_start();
        print("{$v} does not have cover art.");
        $v = preg_replace("/ /", "\ ", $v);
        if (count(glob("*.jpg")) > 0) {
            system("ls \"{$v}/*.jpg\"");
        }
        if (count(glob("*.JPG")) > 0) {
            system("ls \"{$v}/*.JPG\"");
        }
        print("\n\n");
        $content = ob_get_contents();
        file_put_contents("findMissingCoverArt.log", "{$content}\n", FILE_APPEND);
        ob_end_clean();
        print($content);
    }
}

unlink("dir.list");
