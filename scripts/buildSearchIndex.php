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
require_once("StopWords.php");

$cfg = Config::getInstance();
$curdir = getcwd();

chdir($cfg->defaultMp3Dir);
exec("find . -type d | sed 's/^\.\///g' | sort -h > {$cfg->streamsRootDir}/scripts/dir.list");

$f = file("{$cfg->streamsRootDir}/scripts/dir.list");
$c = 0;

/**
 * Config
 */

// You must prepend $curdir to $db, $fdb and $filter or make them absolute paths. These 
// variables will be accessed from various directies and cannot then be relative paths.
$db = "{$curdir}/../search.db";
$fdb = "{$curdir}/../files.db";
$filter = "{$curdir}/filter.json";

/**
 * End Config
 */

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

$c = count($f);
foreach ($f as $k=>$dir) {
    // Base directory
    $dir = trim($dir);

    $start = "$dir:::";
    // List of files.
    $l = "";

    if ($dir != ".") {
        print("[" . ($k+1) . " of $c] " . getcwd() . "/$dir\n");
        chdir($dir);
    }

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
            if ($useFilter && filter($dir . "/" . $orgFile, $a_filter)) {
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

    chdir($cfg->defaultMp3Dir);
}

unlink("{$cfg->streamsRootDir}/scripts/dir.list");
chdir($curdir);
