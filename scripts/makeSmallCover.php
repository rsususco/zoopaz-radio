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

require_once("../lib/Config.php");
$cfg = Config::getInstance();
$curdir = getcwd();

chdir($cfg->defaultMp3Dir);
exec("find . -iname 'cover.jpg' > cover.list");

$f = file("cover.list");
$c = count($f);
foreach ($f as $k=>$l) {
    $l = trim($l);
    $l2 = preg_replace("/cover.jpg$/", "small_cover.jpg", $l);

    if (file_exists($l)) {
        if (!file_exists($l2)) {
            print("[" . ($k+1) . " of $c] {$l}\n");

            print("copy($l, $l2);\n");
            copy($l, $l2);

            print("exec(\"mogrify -resize 175x175 \\\"$l2\\\"\");\n");
            exec("mogrify -resize 175x175 \"$l2\"");

            print("exec(\"mogrify -quality 80 \\\"$l2\\\"\");\n");
            exec("mogrify -quality 80 \"$l2\"");
        }
    }
}

unlink("cover.list");
chdir($curdir);
