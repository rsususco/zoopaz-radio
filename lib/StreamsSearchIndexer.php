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

require_once("getid3/getid3/getid3.php");
require_once("StopWords.php");

class StreamsSearchIndexer {

    private $cfg;
    private $auth;
    private $db;
    private $fdb;
    private $filter;
    private $curdir;
    private $verbose;
    private $dir;
    private $dirlistFile;
    private $addToIndex;

    public function __construct($cfg=null, $auth=null) {
        $this->cfg = $cfg;
        $this->auth = $auth;
        $this->verbose = true;
        $this->addToIndex = false;
        $this->dirlistFile = $this->cfg->dirlistFile;
    }

    public function setCfg($cfg) {
        $this->cfg = $cfg;
    }

    public function getCfg() {
        return $this->cfg;
    }

    public function setAuth($auth) {
        $this->auth = $auth;
    }

    public function getAuth() {
        return $this->auth;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getDb() {
        return $this->db;
    }

    public function setFdb($fdb) {
        $this->fdb = $fdb;
    }

    public function getFdb() {
        return $this->fdb;
    }

    public function setFilter($filter) {
        $this->filter = $filter;
    }

    public function getFilter() {
        return $this->filter;
    }

    public function setCurdir($curdir) {
        $this->curdir = $curdir;
    }

    public function getCurdir() {
        return $this->curdir;
    }

    public function setVerbose($verbose) {
        $this->verbose = $verbose;
    }

    public function getVerbose() {
        return $this->verbose;
    }

    public function setDir($dir) {
        $this->dir = $dir;
    }

    public function getDir() {
        return $this->dir;
    }

    public function setDirlistFile($dirlistFile) {
        $this->dirlistFile = $dirlistFile;
    }

    public function getDirlistFile() {
        return $this->dirlistFile;
    }

    public function setAddToIndex($addToIndex) {
        $this->addToIndex = $addToIndex;
    }

    public function getAddToIndex() {
        return $this->addToIndex;
    }

    public function buildDirList() {
        $this->curdir = getcwd();
        $dir = "";
        if (isset($this->dir)) {
            $dir = $this->dir;
        }
        chdir($this->cfg->defaultMp3Dir);
        if (isset($this->dirlistFile)) {
            exec("find \".{$dir}\" -type d | sed 's/^\.\///g' | sort -h > {$this->dirlistFile}");
        } else {
            exec("find \".{$dir}\" -type d | sed 's/^\.\///g' | sort -h > dir.list");
        }
        chdir($this->curdir);
    }

    /**
     * @tested true
     */
    public function getDirListArray() {
        $f = file($this->dirlistFile);
        return $f;
    }

    /**
     * @tested true
     */
    public function isUsingFilter() {
        $useFilter = false;
        if (file_exists($this->filter)) {
            if ($a_filter = json_decode(file_get_contents($this->filter))) {
                $useFilter = true;
                $o['a_filter'] = $a_filter;
            } else {
                throw new Exception("There seems to be an issue with your $this->filter file.");
            }
        }
        $o['useFilter'] = $useFilter;
        return $o;
    }

    public function getID3() {
        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        return $getID3;
    }

    /**
     * @tested true
     */
    public function index() {
        $this->buildDirList();
        $f = $this->getDirListArray();

        $o = $this->isUsingFilter();
        $a_filter = $o['a_filter'];
        $useFilter = $o['useFilter'];

        chdir($this->cfg->defaultMp3Dir);

        if (!$this->addToIndex) {
            file_put_contents($this->db, "");
            file_put_contents($this->fdb, "");
        }

        $c = count($f);
        foreach ($f as $k=>$dir) {
            // Base directory
            $dir = trim($dir);

            $start = "$dir:::";
            // List of files.
            $l = "";

            if ($dir != ".") {
                if ($this->verbose) {
                    print("[" . ($k+1) . " of $c] " . getcwd() . "/$dir<br />\n");
                }
                if ($this->cfg->logging && !file_exists($dir)) {
                    $this->logfile = $this->streamsRootDir . "/access.log";
                    file_put_contents($cfg->logfile, date("Y-m-d H:i:s") . " ::: " . $_SERVER['REMOTE_ADDR'] . " ::: " 
                            . $_SERVER['HTTP_USER_AGENT'] . " ::: " . $_SERVER['REQUEST_URI'] . " ::: " 
                            . getcwd() . "/{$dir} does not exist.\n", FILE_APPEND);
                }
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
                    if ($useFilter && $this->filter($dir . "/" . $orgFile, $a_filter)) {
                        continue;
                    }
                    file_put_contents($this->fdb, "{$dir}/{$orgFile}\n", FILE_APPEND);
                }

                $file = preg_replace("/\.(mp3|jpg|ogg|m4a|jpeg|png|txt|pdf)/i", "", $file);
                $file = preg_replace("/[^a-zA-Z0-9-_ ,']/", "", $file);
                $file = preg_replace("/\s\s*/", " ", $file);

                $afile = explode(" ", $file);
                $nfile = "";
                foreach ($afile as $cfile) {
                    $cfile = trim($cfile);
                    if (!$this->cfg->disableStopwords && in_array($cfile, StopWords::$stopwords)) {
                        continue;
                    }
                    $nfile .= "$cfile ";
                }
                $file = rtrim($nfile);

                $l .= "{$file} ";
                $al = explode(" ", $l);
                // This is not always a good idea. Quoted search may not always work.
                //$ul = array_unique($al);
                $l = implode(" ", $al);
            }
            unset($files);

            $l = rtrim($start . $l, ":::");

            file_put_contents($this->db, "{$l}\n", FILE_APPEND);

            chdir($this->cfg->defaultMp3Dir);
        }

        unlink("{$this->dirlistFile}");
        chdir($this->curdir);
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
    public function filter($f, $a) {
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

}
