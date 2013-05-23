<?php

exec("find . -type d > dir.list");

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
$c = 0;
foreach ($f as $dir) {
    $dir = trim($dir);
    $mp3 = glob($dir . "/*.{mp3,MP3,ogg,OGG}", GLOB_BRACE);
    $n = count($mp3);
    if ($n < 1) {
        print("$c: Need a montage in $dir\n");
        file_put_contents("m.list", "");
        traverse($dir);
        $m = file("m.list");
        if (count($m) > 0) {
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
            $ms = array_splice($m, 0, $splice);
            $montage = "";
            foreach ($ms as $k=>$mon) {
                $mon = trim($mon);
                $montage .= "\"$mon\" ";
            }
            for ($i=$k+1; $i<$splice; $i++) {
                $montage .= "white.jpg ";
            }
            if ($montage != "") {
                print("system(\"montage -geometry 175x175>+{$splicer}+{$splicer} {$montage} \\\"{$dir}/montage.jpg\\\"\");\n");
                system("montage -geometry 175x175>+{$splicer}+{$splicer} {$montage} \"{$dir}/montage.jpg\"");
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
}
