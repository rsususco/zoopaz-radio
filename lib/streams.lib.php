<?php if (!defined("STREAMS")) { die('CONFIG NOT DEFINED'); }

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

if (!function_exists("handle")) {
    function handle($input) {
        print("<pre>");
        if (is_array($input)) {
            print_r($input);
        } elseif(is_object($input)) {
            var_dump($input);
        } else {
            $input = preg_replace("/\n*$/", "", $input);
            print($input . "\n");
        }
        print("</pre>");
    }
}

function openTheDir($dir) {
    $cfg = Config::getInstance();
    $pageContent = "";
    // This is when you open a dir.
    if (file_exists($cfg->defaultMp3Dir . "/" . $dir . "/cover.jpg")) {

        $adir = explode("/", $dir);
        foreach ($adir as $k=>$d) {
            $adir[$k] = rawurlencode($d);
        }
        $enc_dir = implode("/", $adir);

        $enc_cover = $cfg->defaultMp3Url . singleSlashes("/" . $enc_dir . "/cover.jpg");

        $a_tmpl['enc_cover'] = $enc_cover;
        $pageContent = apply_template("{$cfg->streamsRootDir}/tmpl/coverArt.tmpl", $a_tmpl);
    }
    $pageContent .= getFileIndex($dir);
    return $pageContent;
}

function containsMusic($dir) {
    $cfg = Config::getInstance();
    if (count(glob("{$dir}/*.{" . $cfg->getValidMusicTypes("glob") . "}", GLOB_BRACE)) > 0) {
        return true;
    }
    return false;
}

function buildIndex($a_files, $dirLink, $search=false) {
    $cfg = Config::getInstance();
    $o = array();
    $o['index'] = "";
    $o['isMp3'] = false;
    foreach ($a_files as $k=>$file) {
        if (is_dir($file)) {
            $html_dir = preg_replace("/\"/", "\\\"", $dirLink . $file);
            $html_end_dir = htmlspecialchars($file);

            if (file_exists("{$cfg->defaultMp3Dir}{$dirLink}{$file}/small_montage.jpg")) {
                $background_url = "{$cfg->defaultMp3Url}{$dirLink}{$file}/small_montage.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
            } else if (file_exists("{$cfg->defaultMp3Dir}{$dirLink}{$file}/small_cover.jpg")) {
                $background_url = "{$cfg->defaultMp3Url}{$dirLink}{$file}/small_cover.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
            } else {
                $background_url = "images/bigfolder.png";
                $js_background_url = $background_url;
            }

            $addToPlaylist = "";
            if (containsMusic("{$cfg->defaultMp3Dir}{$dirLink}{$file}")) {
                $a_playlisttmpl = array("html_dir" => $html_dir, "type" => "dir");
                $addToPlaylist = apply_template("{$cfg->streamsRootDir}/tmpl/add-to-playlist.tmpl", $a_playlisttmpl);
            }

            $a_indextmpl = array("js_background_url"=>$js_background_url, "html_dir"=>$html_dir, 
                    "html_end_dir"=>$html_end_dir, "addToPlaylist"=>$addToPlaylist);
            $coverListItem = apply_template("{$cfg->streamsRootDir}/tmpl/coverListItem.tmpl", $a_indextmpl);
            $o['index'] .= $coverListItem;
        } else {
            if (preg_match("/\.(" . $cfg->getValidMusicTypes("preg") . ")$/i", $file)) {
                $o['isMp3'] = true;
                $filesize = human_filesize($file);
                $displayFile = $file;
                $id3 = id3($dirLink, $file);
                $filePath = rawurlencode($dirLink . $file);
                $a_tmpl = array("filePath"=>$filePath, "title"=>$id3['title'], "filesize"=>$filesize);
                $o['index'] .= apply_template("{$cfg->streamsRootDir}/tmpl/albumListItem.tmpl", $a_tmpl);
                continue;
            }
        }
    }
    return $o;
}

function id3($dir, $file) {
    $cfg = Config::getInstance();
    $dir = ltrim(rtrim($dir, "/"), "/");
    $file = ltrim(rtrim($file, "/"), "/");
    $fullPath = $cfg->defaultMp3Dir . "/" . $dir . "/" . $file;
    $getID3 = new getID3();
    $pageEncoding = 'UTF-8';
    $getID3->setOption(array("encoding" => $pageEncoding));
    $id3 = $getID3->analyze($fullPath);
    $o = array();

    $o['playlistTitle'] = "";

    // Set artist
    $artist = getId3Artist($id3);
    if ($artist) {
        $o['artist'] = $artist;
    } else {
        $o['artist'] = "Unknown";
    }
    $o['playlistTitle'] .= $o['artist'] . " &rsaquo; ";

    // Set album
    $album = getId3Album($id3, $dir, $file);
    if ($album) {
        $o['album'] = $album;
    } else {
        $o['album'] = "Unknown";
    }
    $o['playlistTitle'] .= $o['album'] . " &rsaquo; ";

    // Set title
    $title = getId3Title($id3);
    if ($title) {
        $o['title'] = $title;
    } else {
        $o['title'] = $file;
    }
    $o['playlistTitle'] .= $o['title'] . " &rsaquo; ";

    $o['playlistTitle'] = rtrim($o['playlistTitle'], " &rsaquo; ");

    // Set album art
    $o['albumart'] = getAlbumArt($id3, $dir);

    return $o;
}

function getAlbumArt($id3, $dir=null) {
    $cfg = Config::getInstance();
    if (isset($id3) && isset($id3['comments']) && isset($id3['comments']['picture']) 
            && isset($id3['comments']['picture'][0]) && isset($id3['comments']['picture'][0]['data'])) {
        $albumart = "data:image/jpeg;base64," . base64_encode($id3['comments']['picture'][0]['data']);
    } else {
        if (file_exists("{$cfg->defaultMp3Dir}/{$dir}/cover.jpg")) {
            $albumart = "{$cfg->defaultMp3Url}/{$dir}/cover.jpg";
        } else {
            $albumart = "images/bigfolder.png";
        }
    }
    return $albumart;
}

function getId3Artist($id3) {
    $artist = false;
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['artist']) && isset($id3['tags']['id3v2']['artist'][0])) {
        $artist = $id3['tags']['id3v2']['artist'][0];
    }
    return $artist;
}

function getId3Album($id3, $dir=null, $file=null) {
    $cfg = Config::getInstance();
    $album = false;
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['album']) && isset($id3['tags']['id3v2']['album'][0])) {
        $album = $id3['tags']['id3v2']['album'][0];
    }
    return $album;
}

function getId3Title($id3) {
    $title = false;
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['title']) && isset($id3['tags']['id3v2']['title'][0])) {
        $title = $id3['tags']['id3v2']['title'][0];
    }
    return $title;
}

function getFileIndex ($dir) {
    $cfg = Config::getInstance();
    $auth = unserialize($_SESSION['auth']);

    $curdir = getcwd();

    $chdir = "";
    if ($dir === $cfg->defaultMp3Dir) {
        $chdir = $cfg->defaultMp3Dir;
        $dirLink = "";
    } else {
        if (!file_exists($cfg->defaultMp3Dir . '/' . $dir)) {
            return false;
        }
        $chdir = $cfg->defaultMp3Dir . '/' . $dir;
        $_SESSION['currentDir'] = $dir;
        $dirLink = "{$dir}/";
    }

    chdir($chdir);
    $a_files = glob("*");
    natcasesort($a_files);

    $o = buildIndex($a_files, $dirLink);
    $index = $o['index'];
    $isMp3 = $o['isMp3'];

    chdir($curdir);

    if ($dirLink == "") {
        $dir = "";
    }

    $a_dir = preg_split("/\//", $dir);
    $backDirs = "";
    $dirCnt = count($a_dir);
    $cnt = 0;
    $url = "";
    foreach ($a_dir as $k=>$backDir) {
        $url .= "/{$backDir}";

        $enc_url = rawurlencode($url);

        $url = singleSlashes($url);
        $thelinks = getDropDownAlbums($url);
        $a_tmpl = array("backDir" => $backDir);
        if ($dirCnt === 2) {
            // This is every directory after Home that's not the last.
            // Apply the 'enddir' class.
            $a_dir[$k] = apply_template("{$cfg->streamsRootDir}/tmpl/breadcrumb-nodrop.tmpl", $a_tmpl);
        } else if ($cnt === ($dirCnt - 1)) {
            // This is called for every directory after the first clicked directory when in Home
            // including the last directory.
            if ($thelinks) {
                // This is any album in-between the first (first after Home) and last.
                $a_tmpl['thelinks'] = $thelinks;
                $a_dir[$k] = apply_template("{$cfg->streamsRootDir}/tmpl/breadcrumb-withdrop.tmpl", $a_tmpl);
            } else {
                // This is the album you've opened that has music in it. Basically when we
                // call getDropDownAlbums(), if there are now, assume you're at the end.
                // Apply the 'enddir' class.
                $a_dir[$k] = apply_template("{$cfg->streamsRootDir}/tmpl/breadcrumb-nodrop.tmpl", $a_tmpl);
            }
        } else {
            // This is called for every directory.
            // Have drop-down of all available directories under this directory.
            if (isset($url) && strlen($url) > 0) {
                $a_tmpl['url'] = $url;
                $a_tmpl['thelinks'] = $thelinks;
                $a_dir[$k] = apply_template("{$cfg->streamsRootDir}/tmpl/breadcrumb-withdrop-and-url.tmpl", $a_tmpl);
            }
        }
        $cnt++;
    }
    $backDirs = implode(" ", $a_dir);

    // TODO: Break into HTML template
    $controls = "";
    $a_navtmpl = array("backDirs" => $backDirs);
    if (preg_match("/\//", $dir)) {
        $controls = apply_template("{$cfg->streamsRootDir}/tmpl/navigation-nodirs.tmpl", $a_navtmpl);
    } else if ($dir != "") {
        $controls = apply_template("{$cfg->streamsRootDir}/tmpl/navigation-dirs.tmpl", $a_navtmpl);
    } else {
        $controls = "";
    }

    if ($isMp3) {
        $controls .= buildPlayerControls($dir);
    }

    $searchBox = buildSearchBox();

    $a_indextmpl = array("searchBox" => $searchBox, "controls" => $controls, "index" => $index);
    $index = apply_template("{$cfg->streamsRootDir}/tmpl/fileIndex.tmpl", $a_indextmpl);

    return $index;
}

function buildPlayerControls($dir) {
    $cfg = Config::getInstance();
    $enc_dir = rawurlencode($dir);
    $a_tmpl = array("dir" => $dir, "enc_dir" => $enc_dir);
    $controls = apply_template("{$cfg->streamsRootDir}/tmpl/playerControls.tmpl", $a_tmpl);
    return $controls;
}

function buildSearchBox() {
    $cfg = Config::getInstance();
    $html = apply_template("{$cfg->streamsRootDir}/tmpl/searchBox.tmpl", array());
    return $html;
}

function getDropDownAlbums($url) {
    $cfg = Config::getInstance();
    $curdir = getcwd();
    chdir("{$cfg->defaultMp3Dir}/{$url}");
    $a_available_dirs = glob("*", GLOB_ONLYDIR);
    natcasesort($a_available_dirs);
    $thelinks = "";
    foreach ($a_available_dirs as $k5=>$thisdir) {
        $enc_thisdir = urlencode($url . "/" . $thisdir);
        $enc_thisdir = singleSlashes($enc_thisdir);
        $html_thisdir = htmlspecialchars($thisdir);
        $html_thisdir = singleSlashes($html_thisdir);
        $enc_html_thisdir = preg_replace("/\"/", "\\\"", $url . "/" . $thisdir);
        $enc_html_thisdir = singleSlashes($enc_html_thisdir);
        $a_tmpl = array("html_dir" => $html_thisdir, "enc_html_dir" => $enc_html_thisdir);
        if (file_exists("{$thisdir}/small_montage.jpg")) {
            $a_tmpl['src_img'] = "{$cfg->defaultMp3Url}/{$url}/{$html_thisdir}/small_montage.jpg";
        } else if (file_exists("{$thisdir}/small_cover.jpg")) {
            $a_tmpl['src_img'] = "{$cfg->defaultMp3Url}/{$url}/{$html_thisdir}/small_cover.jpg";
        } else {
            $a_tmpl['src_img'] = "images/bigfolder.png";
        }
        $thelinks .= apply_template("{$cfg->streamsRootDir}/tmpl/drop-albums.tmpl", $a_tmpl);
    }
    chdir($curdir);
    if ( $thelinks == "" ) {
        return false;
    }
    return $thelinks;
}

function singleSlashes($in) {
    return preg_replace("/\/\/*/", "/", $in);
}

function print_gzipped_page() {
    global $HTTP_ACCEPT_ENCODING;
    if (headers_sent()) {
        $encoding = false;
    } else if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) {
        $encoding = 'x-gzip';
    } else if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) {
        $encoding = 'gzip';
    } else {
        $encoding = false;
    }

    if ($encoding) {
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    } else {
        ob_end_flush();
        exit();
    }
}

/**
 * Exact phrase search.
 */
function searchArray_v1($regex, $a, $keys=array()) { 
    if(is_array($a)) { 
        foreach($a as $k=>$v) { 
            if(is_array($v)) {
                searchArray($regex, $val, $keys); 
            } else { 
                if(preg_match("/" . preg_quote($regex, "/") . "/i", $v)) {
                    $keys[] = $k; 
                }
            } 
        } 
        return $keys; 
    } 
    return false; 
} 

/**
 * Word searches. Each word is search on and merged with results.
 * Exact phrase searches are possible when wrapped in quotations.
 */
function searchArray($regex, $a, $keys=array()) { 
    if(is_array($a)) { 
        $regex = preg_replace("/\s\s*/", " ", $regex);
        if (preg_match("/\".*?\"/", $regex)) {
            $a_regex[] = preg_replace("/\"(.*?)\"/", "\${1}", $regex);
        } else {
            $a_regex = preg_split("/ /", $regex);
        }
        foreach ($a_regex as $k2=>$word) {
            foreach($a as $k=>$v) { 
                if(is_array($v)) {
                    searchArray($word, $val, $keys); 
                } else { 
                    if(preg_match("/" . preg_quote($word, "/") . "/i", $v)) {
                        $keys[] = $k; 
                    }
                } 
            }
        }
        return $keys; 
    } 
    return false; 
} 

function search($q) {
    $cfg = Config::getInstance();

    $db = "search.db";
    $f = file($db);
    $results = searchArray($q, $f);

    $curdir = getcwd();

    $a_files = array();
    $o['index'] = "";
    $o['isMp3'] = false;
    $index = "";
    foreach ($results as $k=>$key) {
        $r = explode(":::", $f[$key]);
        $dir = $r[0];

        // Don't return directories that don't contain music.
        $cntmusic = count(glob("{$cfg->defaultMp3Dir}/{$dir}/*.{" . $cfg->getValidMusicTypes("glob") . "}", GLOB_BRACE));
        if ($cntmusic < 1) {
            continue;
        }

        if (!file_exists($cfg->defaultMp3Dir . '/' . $dir)) {
            return false;
        }
        $_SESSION['currentDir'] = $dir;
        $dirLink = "/" . preg_replace("/^(.*)\/.*$/", "\${1}", $dir) . "/";

        $reldir = preg_replace("/^.*\/(.*)$/", "\${1}", $dir);;
        $a_files[] = $reldir;
        $chdir = preg_replace("/^(.*)\/.*$/", "\${1}", $cfg->defaultMp3Dir . "/" . $dir);
        chdir($chdir);
        $o = buildIndex($a_files, $dirLink, true);
        $index .= $o['index'];
        unset($o);
        unset($a_files);
        chdir($curdir);
    }

    return $index;
}

function buildArrayFromDir($dir) {
    $cfg = Config::getInstance();

    $curdir = getcwd();

    chdir($cfg->defaultMp3Dir . '/' . $dir);

    $a_files = glob("*.{" . $cfg->getValidMusicTypes("glob") . "}", GLOB_BRACE);
    natcasesort($a_files);
    chdir($curdir);

    return $a_files;
}

function buildPlaylistArrayFromDir($dir, $playlistArray=null) {
    $cfg = Config::getInstance();

    $curdir = getcwd();

    $playlist = array();

    $a_files = buildArrayFromDir($dir);
    natcasesort($a_files);
    foreach ($a_files as $k=>$file) {
        $playlist[] = buildPlaylistItemArray($dir, $file);
    }

    $o = array();
    if ($playlistArray != "") {
        // TODO: Append the new $playlist onto $playlistArray and return.
    } else {
        $o = $playlist;
    }

    return $o;
}

/**
 * @param $playlistArray Pass in a playlist array, and build another playlist from $dir. Append
 * this new playlist to the one passed in and return a new playlist.
 */
function buildPlaylistFromDir($dir, $playlistArray=null) {
    $playlist = buildPlaylistArrayFromDir($dir, $playlistArray);
    $json = json_encode($playlist);
    return $json;
}

// Not yet used.
function buildPlaylistFromArray($playlistArray) {
    $playlist = buildPlaylistArrayFromArray($dir, $playlistArray);
    $json = json_encode($playlist);
    return $json;
}

function buildPlayerAlbumTitle($dir) {
    if (preg_match("/\/.*\//", $dir)) {
        $artist = preg_replace("/^.*\/(.*?)\/.*$/", "\${1}", $dir);
        $html_artist = htmlspecialchars($artist);
        $album = preg_replace("/^.*\/.*?\/(.*)$/", "\${1}", $dir);
        $html_album = htmlspecialchars($album);
        $html_dir = "{$html_artist} &rsaquo; {$html_album}";
    } else {
        $album = preg_replace("/^.*\/(.*)$/", "\${1}", $dir);
        $html_dir = htmlspecialchars($album);
    }
    return $html_dir;
}

function buildPlayerHtml($playlist, $dir, $autoplay='false') {
    $cfg = Config::getInstance();
    $a_indextmpl = array("playlist" => $playlist, "autoplay" => $autoplay);
    $flashPlayer = apply_template("{$cfg->streamsRootDir}/tmpl/jplayer.tmpl", $a_indextmpl);

    // This #theurl span is required. Without it the player javascript
    // doesn't function. The pause button will just restart and play the list.
    $esc_dir = preg_replace("/\\\"/", "\"", $dir);
    $esc_dir = preg_replace("/\"/", "\\\"", $esc_dir);
    $html_dir = buildPlayerAlbumTitle($dir);
    $a_contentplayertmpl = array("esc_dir"=>$esc_dir, "html_dir"=>$html_dir, "flashPlayer"=>$flashPlayer);
    $html = apply_template("{$cfg->streamsRootDir}/tmpl/contentPlayer.tmpl", $a_contentplayertmpl);

    return $html;
}

function addToPlaylist($dir) {
    $auth = unserialize($_SESSION['auth']);
    $currentPlaylistArray = json_decode(file_get_contents($auth->currentPlaylist));
    $toAddJson = buildPlaylistFromDir($dir);
    $toAddArray = json_decode($toAddJson);
    $newPlaylist = array_merge($currentPlaylistArray, $toAddArray);
    $newPlaylistJson = json_encode($newPlaylist);
    file_put_contents($auth->currentPlaylist, $newPlaylistJson);
    file_put_contents($auth->currentPlaylistDir, "/Custom playlist");
    return $toAddJson;
}

function logout() {
    unset($_SESSION['auth']);
    unset($_SESSION);
    session_destroy();
}

function getRandomPlaylistJson($numItems) {
    $cfg = Config::getInstance();
    $db = "files.db";
    $f = file($db);
    $c = count($f);

    // Return if there are now files in the database.
    if ($c === 0) {
        return json_encode(array());
    }

    // Make sure the passed values is an integer.
    $numItems = intval($numItems);

    // Make sure the passed number is less than the total number of files.
    if ($c < $numItems) {
        $numItems = $c;
    } else if ($numItems === 0) {
        $numItems = 1;
    }

    // Make sure $items is an array. array_rand() returns an integer if there's only 1 value.
    if ($numItems === 1) {
        $items = array(array_rand($f, $numItems));
    } else {
        $items = array_rand($f, $numItems);
    }

    // Build the playlist
    $playlist = array();
    foreach ($items as $k=>$key) {
        $audioFile = trim($f[$key]);

        $dir = preg_replace("/^(.*)\/.*$/", "\${1}", $audioFile);
        $file = preg_replace("/^.*\/(.*)$/", "\${1}", $audioFile);

        $playlist[] = buildPlaylistItemArray($dir, $file);
    }

    return json_encode($playlist);
}

function buildPlaylistItemArray($dir, $file) {
    $cfg = Config::getInstance();

    $dir = singleSlashes($dir);
    $file = singleSlashes($file);
    $enc_dir = urlEncodeDir($dir);
    $enc_file = rawurlencode($file);

    $directMusicUrl = "{$cfg->defaultMp3Url}/{$enc_dir}/{$enc_file}";
    $js_directMusicUrl = "{$cfg->defaultMp3Url}/{$enc_dir}/{$enc_file}";
    $id3 = id3($dir, $file);

    $playlist = array("mp3"=>$js_directMusicUrl, "title"=>buildPlaylistTitle($id3, $dir, $file));
    return $playlist;
}

function urlEncodeDir($dir) {
    $adir = explode("/", $dir);
    foreach ($adir as $adk=>$adv) {
        $adir[$adk] = rawurlencode($adv);
    }
    $enc_dir = implode("/", $adir);
    return $enc_dir;
}

function buildPlaylistTitle($id3, $dir, $file) {
    // TODO: Make this a config - show album art in playlist. It does slow things down.
    $enc_dir = preg_replace("/\"/", "\\\"", $dir);
    $enc_file = preg_replace("/\"/", "\\\"", $file);
    $a_tmpl = array("albumart"=>$id3['albumart'], "playlistTitle"=>$id3['playlistTitle'], 
            "dir"=>$enc_dir, "file"=>$enc_file);
    $html = apply_template("tmpl/playlistTitle.tmpl", $a_tmpl);
    return $html;
}

function playRadio($num) {
    $playlist = getRandomPlaylistJson($num);
    $html = buildPlayerHtml($playlist, null, 'true');
    return $html;
}

function createPlaylistJs($dir) {
    $cfg = Config::getInstance();
    $auth = unserialize($_SESSION['auth']);
    $html = "";
    if (file_exists($cfg->defaultMp3Dir . '/' . $dir) && is_dir($cfg->defaultMp3Dir . '/' . $dir)) {
        $playlist = buildPlaylistFromDir($dir);
        file_put_contents($auth->currentPlaylist, $playlist);
        file_put_contents($auth->currentPlaylistDir, $dir);
        $html = buildPlayerHtml($playlist, $dir, 'false');
    }
    return $html;
}
