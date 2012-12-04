<?php

if ( $_SERVER['argv'][1] == "" ) {
    die("You must supply -no (no overwrite) or -o (overwrite)");
}

$overwrite = "";
if ( $_SERVER['argv'][1] == "-o" ) {
    $overwrite = "-o";
}

exec("find . -type d > dir.list");
$a_list = file("dir.list");

foreach ($a_list as $k=>$v) {
    $v = preg_replace("/(\r|\n)/", "", $v);
    exec("/root/src/coverlovin/coverlovin.py \"{$v}\" --size=large --name=cover.jpg {$overwrite}");
}
