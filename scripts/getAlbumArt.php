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

if ( $_SERVER['argv'][1] == "" ) {
    die("You must supply -no (no overwrite) or -o (overwrite)");
}

$overwrite = "";
if ( $_SERVER['argv'][1] == "-o" ) {
    $overwrite = "-o";
}

require_once("../lib/Config.php");
$cfg = Config::getInstance();
$curdir = getcwd();

chdir($cfg->defaultMp3Dir);
exec("find . -type d > dir.list");
$a_list = file("dir.list");

$c = count($a_list);
foreach ($a_list as $k=>$v) {
    print("[$k of $c] {$v}\n");
    $v = preg_replace("/(\r|\n)/", "", $v);
    exec("/root/src/coverlovin/coverlovin.py \"{$v}\" --size=large --name=cover.jpg {$overwrite}");
    print("\n");
}

unlink("dir.list");
chdir($curdir);
