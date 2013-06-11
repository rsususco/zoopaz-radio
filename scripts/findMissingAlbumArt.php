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

/**
 * Config
 */

// This should be an absolute path.
$f = getcwd() . "/findMissingAlbumArt.log";

/**
 * End Config
 */

require_once("../lib/Config.php");
$cfg = Config::getInstance();
$curdir = getcwd();

chdir($cfg->defaultMp3Dir);
exec("find . -type d > dir.list");
$a_list = file("dir.list");

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

print("See {$f} for missing album art log.\n");

unlink("dir.list");
chdir($curdir);
