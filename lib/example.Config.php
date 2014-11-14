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

class Config {
    private static $singleton;
    public function __construct () {
        // This is the root directory that contains your web directories.
        // See the variables below for its use.
        $this->webroot = "/var/www/nas";

        $this->host = "https://www.example.com";

        // This is the root directory of your music archive.
        // Absolute path - no trailing slash.
        $this->defaultMp3Dir = "/var/www/nas/www.example.com/htdocs/music";

        // This is the root URL to the root location of your music archive. {@see $this->defaultMp3Dir}
        // No trailing slash.
        $this->defaultMp3Url = "https://www.example.com/music";

        // This is the root directory of this streaming application.
        // Absolute path - no trailing slash.
        $this->streamsRootDir = "/var/www/nas/www.example.com/htdocs/streams";

        // This is the root URL to the root location of this streaming application. {@see $this->streamsRootDir}
        // No trailing slash.
        $this->streamsRootDirUrl = "https://www.example.com/streams";

        // This is a directory where temporary files are stored for downloading albums.
        // Inside $this->tmpDir a directory named 'downloadAlbum' will be created. Inside that directory
        //     we'll create zip archives for downloading.
        // Install the following cronjob if you want to clean up this directory.
        //     30 5 * * * rm -Rf /tmp/downloadAlbum/*;
        $this->tmpDir = "/tmp";

        // Turn on logging and log to $this->loglocation
        $this->logging = true;
        $this->logfile = "access.log";

        // Valid music types
        $this->validMusicTypes = array("mp3", "m4a", "ogg");

        // Disable stopwords when generating search index
        $this->disableStopwords = false;

        // Maximum number of search results.
        $this->maxSearchResults = 100;

        // Location of search index file.
        $this->searchDatabase = "/var/www/nas/www.example.com/htdocs/streams/search.db";

        // Location of radio files index.
        $this->radioDatabase = "/var/www/nas/www.example.com/htdocs/streams/files.db";

        // Location of radio files index.
        $this->personalRadioDatabase = "default-radio.db";

        // File used by the search indexer temporarily.
        $this->dirlistFile = "/var/www/nas/www.example.com/htdocs/scripts/dir.list";

        // This is used to generate a private key for hashing directories that can
        // be sent directly to people that want to listen without having an account.
        $this->publicListenKey = "ov7w0e8ZAvw@Cj35xH30K2yb6z1wiyN45446!z9@%Q3S4qs0h^ardCkVWQ2^9#!L";

        $this->alternateSessionDir = "/var/lib/php5/streams";

        // This function will be used during authentication.
        $this->hashFunction = "sha1";
    }

    public static function getInstance () {
        if (is_null(self::$singleton)) {
            self::$singleton = new Config();
        }
        return self::$singleton;
    }

    function getValidMusicTypes($type) {
        $ao = array();
        $o = "";
        foreach($this->validMusicTypes as $k=>$t) {
            $t = trim($t);
            $ao[] = strtolower($t);
            $ao[] = strtoupper($t);
        }
        if ($type == "preg") {
            $o = implode("|", $ao);
        } else if ($type == "glob") {
            $o = implode(",", $ao);
        }
        return $o;
    }

    public function collapseChar($char, $dir) {
        return preg_replace("/" . preg_quote($char, "/") 
                . preg_quote($char, "/") . "+/", $char, $dir);
    }

    public function stripLeadingChar($char, $dir) {
        return preg_replace("/^" . preg_quote($char, "/") . "/", "", $dir);
    }

    public function stripTrailingChar($char, $dir) {
        return preg_replace("/" . preg_quote($char, "/") . "$/", "", $dir);
    }

    public function mkdirSafe($dir) {
        if (!file_exists($dir)) {
            mkdir($dir);
        }
    }

    public function mkdirRecursive($dir) {
        $dir = $this->collapseChar("/", $dir);
        $dir = $this->stripLeadingChar("/", $dir);
        $dirs = explode("/", $dir);
        foreach ($dirs as $k=>$d) {
            if ($k === 0) {
                $tomake = "/" . $d;
            } else {
                $tomake = $tomake . "/" . $d;
            }
            if (preg_match("/^" . preg_quote($this->cfg->rootDir, "/") . "\/.+/", $tomake)) {
                $this->mkdirSafe($tomake);
            }
        }
    }
}
