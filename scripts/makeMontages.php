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

$in = ".";
if (isset($_SERVER['argv'][1])) {
    $in = $_SERVER['argv'][1];
}

require_once("../lib/Config.php");
$cfg = Config::getInstance();
$curdir = getcwd();

chdir($cfg->defaultMp3Dir);
exec("find {$in} -type d > dir.list");

function traverse($dir) {
    $f = glob("$dir/*");
    foreach ($f as $file) {
        $file = trim($file);
        if (is_dir($file)) {
            traverse($file);
        } else {
            if (preg_match("/small_cover.jpg$/i", $file)) {
                file_put_contents("m.list", "$file\n", FILE_APPEND);
            }
        }
    }
}

file_put_contents("m.list", "");
$f = file("dir.list");
$numDirs = count($f);
$c = 0;
$realC = 0;
foreach ($f as $dir) {
    $dir = trim($dir);
    $mp3 = glob($dir . "/*.{m4a,M4A,mp3,MP3,ogg,OGG}", GLOB_BRACE);
    $n = count($mp3);
    if ($n < 1) {
        print("[" . ($realC+1) . " of {$numDirs} (actual montage created {$c}]\nNeed a montage in {$dir}\n\n");
        file_put_contents("m.list", "");
        traverse($dir);
        $m = file("m.list");
        if (count($m) > 0) {
            /*
            if (count($m) < 2) {
                $splice = 1;
                $splicer = 1;
            } else if (count($m) < 5) {
                $splice = 4;
                $splicer = 2;
            } else {
                $splice = 9;
                $splicer = 3;
            }
            */
            if (count($m) < 4) {
                $splice = 1;
                $splicer = 1;
            } else if (count($m) >= 4 && count($m) < 9) {
                $splice = 4;
                $splicer = 2;
            } else {
                $splice = 9;
                $splicer = 3;
            }
            $ms = array_splice($m, 0, $splice);
            $montage = "";
            foreach ($ms as $k=>$mon) {
                $mon = trim($mon);
                $montage .= "\"$mon\" ";
            }
            for ($i=$k+1; $i<$splice; $i++) {
                $montage .= "{$cfg->streamsRootDir}/scripts/white.jpg ";
            }
            if ($montage != "") {
                print("system(\"montage -geometry 175x175+{$splicer}+{$splicer} {$montage} \\\"{$dir}/montage.jpg\\\"\");\n");
                system("montage -geometry 175x175+{$splicer}+{$splicer} {$montage} \"{$dir}/montage.jpg\"");
                print("copy(\"{$dir}/montage.jpg\", \"{$dir}/small_montage.jpg\");\n");
                copy("{$dir}/montage.jpg", "{$dir}/small_montage.jpg");
                print("system(\"mogrify -resize 175x175 \\\"{$dir}/small_montage.jpg\\\"\");\n");
                system("mogrify -resize 175x175 \"{$dir}/small_montage.jpg\"");
            }
        } else {
            print("Skipping $dir no covers.\n");
        }
        $c++;
    }
    $realC++;
}

unlink("dir.list");
chdir($curdir);
