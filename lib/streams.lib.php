<?php if (!defined("STREAMS")) { die('CONFIG NOT DEFINED'); }

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

        $pageContent .= "<div class=\"coverart\"><a target=\"_blank\" href=\"{$enc_cover}\"><img src=\"{$enc_cover}\" alt=\"cover\" /></a></div><span class=\"clear\"></span>";
    }
    $pageContent .= getFileIndex($dir);
    return $pageContent;
}

function containsMusic($dir) {
    if (count(glob("{$dir}/*.{mp3,MP3,ogg,OGG,m4a,M4A}", GLOB_BRACE)) > 0) {
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
            $html_data_url = preg_replace("/\"/", "\\\"", $dirLink . $file);
            $html_file = htmlspecialchars($file);

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

            // TODO: Break into HTML template
            $addToPlaylist = "";
            if (containsMusic("{$cfg->defaultMp3Dir}{$dirLink}{$file}")) {
                $addToPlaylist = "<span class=\"linkbutton addtoplaylist\" data-url=\"{$html_data_url}\">add to playlist</span>";
            }

            $a_indextmpl = array("js_background_url"=>$js_background_url, "html_data_url"=>$html_data_url, 
                    "html_file"=>$html_file, "addToPlaylist"=>$addToPlaylist);
            $coverListItem = apply_template("{$cfg->streamsRootDir}/tmpl/coverListItem.tmpl", $a_indextmpl);
            $o['index'] .= $coverListItem;
        } else {
            if (preg_match("/\.(m4a|mp3|ogg|flac)$/i", $file)) {
                $o['isMp3'] = true;
                $filesize = human_filesize($file);
                $displayFile = $file;

                $id3 = id3($dirLink, $file);

                // TODO: Break into HTML template
                $o['index'] .= "<li class='mp3'><span class=\"text\"><a target=\"_blank\" href=\"index.php?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($id3['title']) . "</a> <code>{$filesize}</code></span></li>";
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
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['artist']) && isset($id3['tags']['id3v2']['artist'][0])) {
        $artist = $id3['tags']['id3v2']['artist'][0];
        $o['playlistTitle'] .= $artist . " &rsaquo; ";
    } else {
        $artist = "Unknown";
    }
    $o['artist'] = $artist;

    // Set album
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['album']) && isset($id3['tags']['id3v2']['album'][0])) {
        $album = $id3['tags']['id3v2']['album'][0];
        $o['playlistTitle'] .= $album . " &rsaquo; ";
    } else {
        $album = "Unknown";
    }
    $o['album'] = $album;

    // Set title
    if (isset($id3) && isset($id3['tags']) && isset($id3['tags']['id3v2']) 
            && isset($id3['tags']['id3v2']['title']) && isset($id3['tags']['id3v2']['title'][0])) {
        $title = $id3['tags']['id3v2']['title'][0];
    } else {
        $title = $file;
    }
    $o['playlistTitle'] .= $title . " &rsaquo; ";
    $o['playlistTitle'] = rtrim($o['playlistTitle'], " &rsaquo; ");
    $o['title'] = $title;

    // Set album art
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
    $o['albumart'] = $albumart;
    return $o;
}

function getFileIndex ($dir) {
    $cfg = Config::getInstance();

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
        if ($cnt === 0) {
            $url .= $backDir;
        } else {
            $url .= "/{$backDir}";
        }
        $enc_url = urlencode($url);
        // TODO: Break into HTML template
        if ($dirCnt === 1) {
            $a_dir[$k] = "<span class='filesize_type'><span class=\"enddir\">{$backDir}</span></span>";
        } else if ($cnt === ($dirCnt - 1)) {
            // Have drop-down of all available directories under this directory.
            $thelinks = getDropDownAlbums($url);
            if ($thelinks) {
                $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\">{$backDir}<div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
            } else {
                $a_dir[$k] = "<span class='filesize_type'><span class='enddir'>{$backDir}</span></span><!--span.filesize_type-->";
            }
        } else {
            // Have drop-down of all available directories under this directory.
            if (isset($url) && strlen($url) > 0) {
                $thelinks = getDropDownAlbums($url);
                $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a class=\"dirlink\" data-url=\"{$url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
            }
        }
        $cnt++;
    }
    $backDirs = implode(" ", $a_dir);

    $createPlaylistLink = "";
    if ($isMp3) {
        $createPlaylistLink = "<a id=\"playbutton\" class=\"button\" style='cursor:pointer;' data-url=\"" . $dir . "\">Play</a>";
    }

    // TODO: Break into HTML template
    if (preg_match("/\//", $dir)) {
        $previousDir = preg_replace("/^(.+)\/(.*)$/", "$1", $dir);
        $previousDirListItem = "<li class='previousDirectoryListItem'><span class='filesize_type'><a class=\"dirlink\" data-url=\"\">Home</a></span> {$backDirs}</li>";
        if (count(glob("{$cfg->defaultMp3Dir}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0) {
            $previousDirListItem .= "<li class='previousDirectoryListItem' id='playercontrols'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"index.php?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else if ($dir != "") {
        $previousDir = $dir;
        $previousDirListItem = "<li class='previousDirectoryListItem'>{$backDirs}</li>";
        if (count(glob("{$cfg->defaultMp3Dir}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0) {
            $previousDirListItem .= "<li class='previousDirectoryListItem' id='playercontrols'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"index.php?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else {
        $previousDir = "";
        $previousDirListItem = "";
    }

    $searchBox = buildSearchBox();

    // TODO: Break into HTML template
    $index = "{$searchBox}<ul id=\"navlist\">{$previousDirListItem}</ul><div class=\"clear\"></div><ul id=\"musicindex\">" . $index . "</ul>";

    return $index;
}

function buildSearchBox() {
    // TODO: Break into HTML template
    $html = <<<eof
<div id="searchbox">
    <input class="button" id="radio-button" type="button" value="Radio" /> <input class="button" id="logout-link" type="button" value="Logout" /> <input type="text" id="search" placeholder="Find some music..." />
</div><!--div#searchbox-->
eof;
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
        // TODO: Break into HTML template
        if (file_exists("{$thisdir}/small_montage.jpg")) {
            $thelinks .= "<div class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$cfg->defaultMp3Url}/{$url}/{$html_thisdir}/small_montage.jpg\" alt=\"img\" /> <div class=\"droplink-text\">{$html_thisdir}</div></div>"; 
        } else if (file_exists("{$thisdir}/small_cover.jpg")) {
            $thelinks .= "<div class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$cfg->defaultMp3Url}/{$url}/{$html_thisdir}/small_cover.jpg\" alt=\"img\" /> <div class=\"droplink-text\">{$html_thisdir}</div></div>"; 
        } else {
            $thelinks .= "<div class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"images/bigfolder.png\" alt=\"img\" /> <div class=\"droplink-text\">{$html_thisdir}</div></div>"; 
        }
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
        $cntmusic = count(glob("{$cfg->defaultMp3Dir}/{$dir}/*.{mp3,MP3,ogg,OGG,m4a,M4A}", GLOB_BRACE));
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

    $a_files = glob("*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE);
    natcasesort($a_files);
    chdir($curdir);

    return $a_files;
}

function buildPlaylistArrayFromDir($dir, $playlistArray=null) {
    $cfg = Config::getInstance();

    $curdir = getcwd();

    $adir = explode("/", $dir);
    foreach ($adir as $adk=>$adv) {
        $adir[$adk] = rawurlencode($adv);
    }
    $tdir = implode("/", $adir);
    $playlist = array();

    $a_files = buildArrayFromDir($dir);
    natcasesort($a_files);
    foreach ($a_files as $k=>$mp3) {
        $amp3 = rawurlencode($mp3);
        $directMusicUrl = "{$cfg->defaultMp3Url}/{$tdir}/{$amp3}";
        $js_directMusicUrl = "{$cfg->defaultMp3Url}/{$tdir}/{$amp3}";
        $id3 = id3(rawurldecode($tdir), rawurldecode($amp3));
        $playlist[] = array("mp3"=>$js_directMusicUrl, "title"=>"<img style=\"width:2em; height:2em;\" src=\"{$id3['albumart']}\" /> {$id3['playlistTitle']}");
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
    $a_indextmpl = array("playlist" => $playlist, "autoplay" => $autoplay);
    $flashPlayer = apply_template("tmpl/jplayer.tmpl", $a_indextmpl);

    // This #theurl span is required. Without it the player javascript
    // doesn't function. The pause button will just restart and play the list.
    $esc_dir = preg_replace("/\\\"/", "\"", $dir);
    $esc_dir = preg_replace("/\"/", "\\\"", $esc_dir);
    $html_dir = buildPlayerAlbumTitle($dir);
    $a_contentplayertmpl = array("esc_dir"=>$esc_dir, "html_dir"=>$html_dir, "flashPlayer"=>$flashPlayer);
    $html = apply_template("tmpl/contentPlayer.tmpl", $a_contentplayertmpl);

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

        // URLEncode dir/file
        $aaudioFile = explode("/", $audioFile);
        foreach ($aaudioFile as $adk=>$adv) {
            $aaudioFile[$adk] = rawurlencode($adv);
        }
        $taudioFile = implode("/", $aaudioFile);

        $directMusicUrl = "{$cfg->defaultMp3Url}/{$taudioFile}";
        $js_directMusicUrl = "{$cfg->defaultMp3Url}/{$taudioFile}";

        $dir = preg_replace("/^(.*)\/.*$/", "\${1}", $audioFile);
        $mp3 = preg_replace("/^.*\/(.*)$/", "\${1}", $audioFile);
        $id3 = id3($dir, $mp3);
        $playlist[] = array("mp3"=>$js_directMusicUrl, "title"=>"<img style=\"width:2em; height:2em;\" src=\"{$id3['albumart']}\" /> {$id3['playlistTitle']}");
    }
    return json_encode($playlist);
}

function playRadio($num) {
    $playlist = getRandomPlaylistJson($num);
    $html = buildPlayerHtml($playlist, null, 'true');
    return $html;
}
