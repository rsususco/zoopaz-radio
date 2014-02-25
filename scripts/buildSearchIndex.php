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
require_once("../lib/StreamsSearchIndexer.php");

$cfg = Config::getInstance();
$auth = new Auth();
$indexer = new StreamsSearchIndexer($cfg, $auth);

/**
 * Config
 */

// You must prepend $curdir to $db, $fdb and $filter or make them absolute paths. These 
// variables will be accessed from various directies and cannot then be relative paths.
$curdir = getcwd();
$db = "{$curdir}/../search.db";
$fdb = "{$curdir}/../files.db";
$filter = "{$curdir}/filter.json";

$indexer->setDb($cfg->searchDatabase);
$indexer->setFdb($cfg->radioDatabase);
$indexer->setFilter($filter);
$indexer->index();
