<?php

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
    $pageContent = "";
    // This is when you open a dir.
    if (file_exists($GLOBALS['defaultMp3Dir'] . "/" . $_GET['dir'] . "/cover.jpg")) {
        $pageContent .= "<div class=\"coverart\"><a target=\"_blank\" href=\"../music/{$_GET['dir']}"
                . "/cover.jpg\"><img src=\"../music/{$_GET['dir']}/cover.jpg\" alt=\"cover\" /></a>"
                . "</div><span class=\"clear\"></span>";
    }
    $pageContent .= getFileIndex($dir);
    return $pageContent;
}

function containsMusic($dir) {
    if (glob("{$dir}/*.{mp3,MP3,ogg,OGG,m4a,M4A}", GLOB_BRACE) > 0) {
        return true;
    }
    return false;
}

function buildIndex($a_files, $dirLink, $search=false) {
    $o = array();
    $o['index'] = "";
    $o['isMp3'] = false;
    foreach ($a_files as $k=>$file) {
        if (is_dir($file)) {
            $html_data_url = preg_replace("/\"/", "\\\"", $dirLink . $file);
            $html_file = htmlspecialchars($file);

            if (file_exists("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}/small_montage.jpg")) {
                $background_url = "{$GLOBALS['defaultMp3Url']}/{$dirLink}{$file}/small_montage.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
            } else if (file_exists("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}/small_cover.jpg")) {
                $background_url = "{$GLOBALS['defaultMp3Url']}/{$dirLink}{$file}/small_cover.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
            } else {
                $background_url = "images/bigfolder.png";
                $js_background_url = $background_url;
            }

            $addToPlaylist = "";
            if (containsMusic("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}")) {
                $addToPlaylist = "<span onclick=\"addToPlaylist(this)\" class=\"linkbutton addtoplaylist\" data-url=\"{$html_data_url}\">add to playlist</span>";
            }

            $coverListItem = <<<eof
<li class="dirlink-cover dirlinkcover" style="background:url('{$js_background_url}') no-repeat left center; background-size:128px 128px;" data-url="{$html_data_url}">
    <div class="linknamediv">
        <div class="dirtext">
            <a class="linkname">{$html_file}</a> 
            {$addToPlaylist}
        </div>
    </div><!--div.linknamediv-->
    <div class="clear"></div><!--div.clear-->
</li>
eof;
            $o['index'] .= $coverListItem;
        } else {
            if (preg_match("/\.(m4a|mp3|ogg|flac)$/i", $file)) {
                $o['isMp3'] = true;
                $filesize = human_filesize($file);
                $displayFile = $file;

                $o['index'] .= "<li class='mp3'><span class=\"text\"><a target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></span></li>";
                continue;
            }
        }
    }
    return $o;
}

function getFileIndex ($dir) {
    $curdir = getcwd();

    $chdir = "";
    if ($dir === $GLOBALS['defaultMp3Dir']) {
        $chdir = $GLOBALS['defaultMp3Dir'];
        $dirLink = "";
    } else {
        if (!file_exists($GLOBALS['defaultMp3Dir'] . '/' . $dir)) {
            return false;
        }
        $chdir = $GLOBALS['defaultMp3Dir'] . '/' . $dir;
        $_SESSION['currentDir'] = $dir;
        $dirLink = "{$dir}/";
    }

    chdir($chdir);
    $a_files = glob("*");

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
            $thelinks = getDropDownAlbums($url);
            $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a class=\"dirlink\" data-url=\"{$url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
        }
        $cnt++;
    }
    //$backDirs = implode(" &rsaquo;<!--&raquo;--> ", $a_dir);
    $backDirs = implode(" ", $a_dir);

    $createPlaylistLink = "";
    if ($isMp3) {
        $createPlaylistLink = "<a id=\"playbutton\" class=\"button\" style='cursor:pointer;' data-url=\"" . $dir . "\">Play</a>";
    }

    if (preg_match("/\//", $dir)) {
        $previousDir = preg_replace("/^(.+)\/(.*)$/", "$1", $dir);
        $previousDirListItem = "<li class='previousDirectoryListItem'><span class='filesize_type'><a class=\"dirlink\" data-url=\"\">Home</a></span> {$backDirs}</li>";
        if (count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0) {
            $previousDirListItem .= "<li class='previousDirectoryListItem' id='playercontrols'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else if ($dir != "") {
        $previousDir = $dir;
        $previousDirListItem = "<li class='previousDirectoryListItem'>{$backDirs}</li>";
        if (count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0) {
            $previousDirListItem .= "<li class='previousDirectoryListItem' id='playercontrols'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else {
        $previousDir = "";
        $previousDirListItem = "";
    }

    $searchBox = buildSearchBox();

    $index = "{$searchBox}<ul id=\"navlist\">{$previousDirListItem}</ul><div class=\"clear\"></div><ul id=\"musicindex\">" . $index . "</ul>";

    return $index;
}

function buildSearchBox() {
    $html = <<<eof
<div id="searchbox">
<input type="text" id="search" placeholder="Find some music..." />
</div><!--div#searchbox-->
eof;
    return $html;
}

function getDropDownAlbums($url) {
    $curdir = getcwd();
    chdir("{$GLOBALS['defaultMp3Dir']}/{$url}");
    $a_available_dirs = glob("*", GLOB_ONLYDIR);
    $thelinks = "";
    foreach ($a_available_dirs as $k5=>$thisdir) {
        $enc_thisdir = urlencode($url . "/" . $thisdir);
        $enc_thisdir = singleSlashes($enc_thisdir);
        $html_thisdir = htmlspecialchars($thisdir);
        $html_thisdir = singleSlashes($html_thisdir);
        $enc_html_thisdir = preg_replace("/\"/", "\\\"", $url . "/" . $thisdir);
        $enc_html_thisdir = singleSlashes($enc_html_thisdir);
        if (file_exists("{$thisdir}/small_montage.jpg")) {
            $thelinks .= "<div class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$GLOBALS['defaultMp3Url']}/{$url}/{$html_thisdir}/small_montage.jpg\" alt=\"img\" /> <div class=\"droplink-text\">{$html_thisdir}</div></div>"; 
        } else if (file_exists("{$thisdir}/small_cover.jpg")) {
            $thelinks .= "<div class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$GLOBALS['defaultMp3Url']}/{$url}/{$html_thisdir}/small_cover.jpg\" alt=\"img\" /> <div class=\"droplink-text\">{$html_thisdir}</div></div>"; 
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
        $cntmusic = count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{mp3,MP3,ogg,OGG,m4a,M4A}", GLOB_BRACE));
        if ($cntmusic < 1) {
            continue;
        }

        if (!file_exists($GLOBALS['defaultMp3Dir'] . '/' . $dir)) {
            return false;
        }
        $_SESSION['currentDir'] = $dir;
        $dirLink = "/" . preg_replace("/^(.*)\/.*$/", "\${1}", $dir) . "/";

        $reldir = preg_replace("/^.*\/(.*)$/", "\${1}", $dir);;
        $a_files[] = $reldir;
        $chdir = preg_replace("/^(.*)\/.*$/", "\${1}", $GLOBALS['defaultMp3Dir'] . "/" . $dir);
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
    $curdir = getcwd();

    chdir($GLOBALS['defaultMp3Dir'] . '/' . $dir);

    $a_files = glob("*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE);
    chdir($curdir);

    return $a_files;
}

function buildPlaylistArrayFromDir($dir, $playlistArray=null) {
    $curdir = getcwd();

    $fileName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $dir);
    $filename = preg_replace("/__+/", "_", $filename);

    chdir($GLOBALS['streamsDir']);

    $adir = explode("/", $dir);
    foreach ($adir as $adk=>$adv) {
        $adir[$adk] = rawurlencode($adv);
    }
    $tdir = implode("/", $adir);
    $playlist = array();

    $a_files = buildArrayFromDir($dir);
    foreach ($a_files as $k=>$mp3) {
        $enc_file = htmlspecialchars($dir . '/' . $mp3);

        $amp3 = rawurlencode($mp3);
        $directMusicUrl = "{$GLOBALS['defaultMp3Url']}/{$tdir}/{$amp3}";
        $js_directMusicUrl = "{$GLOBALS['defaultMp3Url']}/{$tdir}/{$amp3}";

        $js_mp3 = preg_replace("/'/", "\\\'", $mp3);
        $playlist[] = array("file"=>$js_directMusicUrl, "title"=>$js_mp3);
    }

    chdir($curdir);

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

function buildPlayerHtml($playlist, $dir, $autoplay='false') {
    $a_indextmpl = array("playlist" => $playlist, "autoplay" => $autoplay);
    $flashPlayer = apply_template("tmpl/player.tmpl", $a_indextmpl);

    // This #theurl span is required. Without it the player javascript
    // doesn't function. The pause button will just restart and play the list.
    $esc_dir = preg_replace("/\\\"/", "\"", $dir);
    $esc_dir = preg_replace("/\"/", "\\\"", $esc_dir);
    $html = <<<eof
<span id="theurl" data-url="{$esc_dir}" />
<div class='m3uplayer'><table>
<tr><td class="currentsong">Current song: <span id="currentSong"></span> &#160;&#160;&#160; 
<a style="cursor:pointer; text-decoration:underline;" onclick="shufflePlaylist()">shuffle</a></td></tr>
<tr><td id="mediaplayer"></td></tr>
</table>
{$flashPlayer}
</span>
eof;
    return $html;
}
