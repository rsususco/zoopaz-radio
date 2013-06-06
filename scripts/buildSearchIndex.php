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

exec("find . -type d | sed 's/^\.\///g' > dir.list");

$f = file("dir.list");
$c = 0;
$curdir = getcwd();

/**
 * Config
 */

$db = "{$curdir}/../streams/search.db";
$fdb = "{$curdir}/../streams/files.db";
$filter = "{$curdir}/filter.json";

/**
 * End Config
 */

require_once("StopWords.php");

$useFilter = false;
if (file_exists($filter)) {
    if ($a_filter = json_decode(file_get_contents($filter))) {
        $useFilter = true;
    } else {
        die("There seems to be an issue with your $filter file.");
    }
}

/**
 * $a should be a json string read into an object via json_decode(). A
 * sample json string is as follows: <br />
 * {@code
 * <pre>
 * {
 *     "filter": {
 *         "include": [
 *             "/Phish/i"
 *         ],
 *         "exclude": [
 *             "/Bogus/i"
 *         ]
 *     }
 * }
 * </pre> } <br />
 * Include always comes before exclude. The include and exclude arrays
 * should contain preg_ style regular expressions and should begin and
 * end with slashes. e.g. /^some regex$/i and can include modifiers
 * such as i or g...
 * @param $f String Path to file.
 * @param $a Object The filter object read from $filter.
 * @return boolean If true then we do not include the file in files.db.
 */
function filter($f, $a) {
    foreach ($a->filter->include as $regex) {
        if (preg_match($regex, $f)) {
            return false;
        }
    }
    foreach ($a->filter->exclude as $regex) {
        if (preg_match($regex, $f)) {
            return true;
        }
    }
    return false;
}

file_put_contents($db, "");
file_put_contents($fdb, "");

foreach ($f as $dir) {
    // Base directory
    $dir = trim($dir);

    $start = "$dir:::";
    // List of files.
    $l = "";

    chdir($dir);

    $files = glob("*");
    foreach ($files as $file) {
        $file = trim($file);
        $orgFile = $file;
        $file = strtolower($file);
        if (preg_match("/^(cover|small_cover|montage|small_montage).jpg$/", $file)) {
            continue;
        }

        // Audio file matched
        if (preg_match("/\.(mp3|m4a|ogg)$/i", $orgFile)) {
            if (filter($dir . "/" . $orgFile, $a_filter)) {
                continue;
            }
            file_put_contents($fdb, "{$dir}/{$orgFile}\n", FILE_APPEND);
        }

        $file = preg_replace("/\.(mp3|jpg|ogg|m4a|jpeg|png|txt|pdf)/i", "", $file);
        $file = preg_replace("/[0-9~!@#\$%\^\&\*\(\)_\+`\-\.\']/", "", $file);
        $file = preg_replace("/\s\s*/", " ", $file);

        $afile = explode(" ", $file);
        $nfile = "";
        foreach ($afile as $cfile) {
            if (in_array($cfile, StopWords::$stopwords)) {
                continue;
            }
            $nfile = "$cfile ";
        }
        $file = rtrim($nfile);

        $l .= "{$file} ";
        $al = explode(" ", $l);
        $ul = array_unique($al);
        $l = implode(" ", $ul);
    }
    unset($files);

    $l = rtrim($start . $l, ":::");

    file_put_contents($db, "{$l}\n", FILE_APPEND);

    chdir($curdir);
}
