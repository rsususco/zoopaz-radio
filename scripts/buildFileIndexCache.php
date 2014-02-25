<?php

/*
Copyright 2014 Weldon Sams

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
require_once("../lib/Auth.php");
require_once("../lib/Streams.php");
require_once("../lib/WsTmpl.php");

$cfg = Config::getInstance();
$auth = new Auth();
$t = new WsTmpl();
$streams = new Streams($cfg, $auth, $t);

/**
 * Config
 */

$curdir = getcwd();
chdir($cfg->defaultMp3Dir);

$hash = sha1(microtime() . date());

exec("find . -type d > {$hash}.tmp");

$dirs = file("{$hash}.tmp");
$cnt = count($dirs);
foreach ($dirs as $k=>$dir) {
    $dir = preg_replace("/^\./", "", trim($dir));
    $streams->buildFileIndexCache($dir);
    print("[" . ($k+1) . " of {$cnt}] {$dir}\n");
}

unlink("{$hash}.tmp");
chdir($curdir);
