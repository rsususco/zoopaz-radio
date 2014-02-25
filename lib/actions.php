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

if ($_GET['action'] == "downloadAlbum" && $_GET['dir'] != "") {
    if (is_dir($cfg->defaultMp3Dir . "/" . $_GET['dir'])) {
        $theDir = preg_replace("/^.+\/(.+)$/i", "\${1}", $_GET['dir']);
        $curdir = getcwd();
        $musicDir = $cfg->defaultMp3Dir . $_GET['dir'];
        $musicContDir = preg_replace("/^(.*)\/.*$/", "\${1}", $musicDir);
        $musicZipDir = preg_replace("/^.*\/(.*)$/", "\${1}", $musicDir);
        $musicZip = preg_replace("/[^0-9a-zA-Z-_]/", "_", $musicZipDir);
        $musicTmpDir = "{$cfg->tmpDir}/streamsTmpDir/{$auth->username}{$musicDir}";
        if (!file_exists($musicTmpDir)) {
            mkdir($musicTmpDir, 0777, true);
        }
        if (file_exists($musicContDir)) {
            chdir($musicContDir);
        } else {
            die("Could not find music.");
        }
        exec("zip -r \"{$musicTmpDir}/{$musicZip}.zip\" \"{$musicZipDir}\"");
        header('Content-Description: Download file');
        header("Content-type: application/x-download");
        header("Content-Length: " . filesize("{$musicTmpDir}/{$musicZip}.zip"));
        header("Content-Disposition: attachment; filename=" . basename("{$musicZip}.zip"));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
        readfile("{$musicTmpDir}/{$musicZip}.zip");
        exec("rm -Rf {$cfg->tmpDir}/streamsTmpDir/{$auth->username}");
        chdir($curdir);
        die();
    }
} else if ($_GET['action'] == "download") {
    $filename = preg_replace("/^.*\/(.*)$/i", "$1", $_GET['file']);
    header("Content-type: applications/x-download");
    header("Content-Length: " . filesize($cfg->defaultMp3Dir . '/' . $_GET['file']));
    header("Content-Disposition: attachment; filename=" . basename($filename));
    readfile($cfg->defaultMp3Dir . '/' . $_GET['file']);
    die();
}
