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

if ($_GET['action'] == "downloadAlbum" && $_GET['dir'] != "") {
    if (is_dir($cfg->defaultMp3Dir . "/" . $_GET['dir'])) {
        $theDir = preg_replace("/^.+\/(.+)$/i", "\${1}", $_GET['dir']);
        $curdir = getcwd();
        chdir($cfg->defaultMp3Dir . "/" . $_GET['dir']);
        chdir("..");
        if (!file_exists("{$cfg->tmpDir}/downloadAlbum")) {
            mkdir("{$cfg->tmpDir}/downloadAlbum");
        }
        $md5 = md5(date("Y-m-dH:i:s") . microtime() . rand(0,999));
        mkdir("{$cfg->tmpDir}/downloadAlbum/{$md5}");
        exec("cp -Rf \"{$theDir}\" \"{$cfg->tmpDir}/downloadAlbum/{$md5}/{$theDir}\"");
        chdir("{$cfg->tmpDir}/downloadAlbum/{$md5}");
        exec("zip -r \"{$theDir}.zip\" \"{$theDir}\"");
        header('Content-Description: Download file');
        header("Content-type: application/zip");
        header("Content-Length: " . filesize("{$theDir}.zip"));
        header("Content-Disposition: attachment; filename=" . basename("{$theDir}.zip"));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
        readfile("{$theDir}.zip");
        exec("rm -Rf {$cfg->tmpDir}/downloadAlbum/{$md5}");
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
