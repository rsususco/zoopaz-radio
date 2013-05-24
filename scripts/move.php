<?php
$files = glob("*.mp3");
foreach ($files as $k=>$file) {
    if ( preg_match("/^-/", $file) ) {
        $newfile = preg_replace("/^-/", "", $file);
        print("rename($file, $newfile)\n");
        rename($file, $newfile);
    }
}
