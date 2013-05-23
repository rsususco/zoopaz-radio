<?php

exec("find . -iname '* ' > end-space.list");

$f = file("end-space.list");
foreach ($f as $k=>$file) {
    $old = preg_replace("/(\r|\n)/", "", $file);
    $new = preg_replace("/ *$/", "", $old);
    print("mv \"$old\" \"$new\"\n");
    rename($old, $new);
}
