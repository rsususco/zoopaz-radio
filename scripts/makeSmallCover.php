<?php

exec("find . -iname 'cover.jpg' > cover.list");

$f = file("cover.list");

foreach ($f as $l) {
    $l = trim($l);
    $l2 = preg_replace("/cover.jpg$/", "small_cover.jpg", $l);

    if (file_exists($l)) {
        if (!file_exists($l2)) {
            print("copy($l, $l2);\n");
            copy($l, $l2);

            print("exec(\"mogrify -resize 175x175 \\\"$l2\\\"\");\n");
            exec("mogrify -resize 175x175 \"$l2\"");

            print("exec(\"mogrify -quality 80 \\\"$l2\\\"\");\n");
            exec("mogrify -quality 80 \"$l2\"");
        }
    }
}

