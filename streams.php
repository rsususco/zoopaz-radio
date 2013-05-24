<?php
define("STREAMS", 1);

session_start();
$sessid = session_id();

require_once("auth.php");
require_once("config.php");

ob_start("ob_gzhandler");

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

require_once("WsPhpLibrary.php");

function openTheDir() {
    $pageContent = "";
    // This is when you open a dir.
    if (file_exists($GLOBALS['defaultMp3Dir'] . "/" . $_GET['dir'] . "/cover.jpg")) {
        $pageContent .= "<div class=\"coverart\"><a target=\"_blank\" href=\"../music/{$_GET['dir']}"
                . "/cover.jpg\"><img src=\"../music/{$_GET['dir']}/cover.jpg\" alt=\"cover\" /></a>"
                . "</div><span class=\"clear\"></span>";
    }
    $pageContent .= getFileIndex($_GET['dir']);
    return $pageContent;
}

if ($logging) {
    file_put_contents($logfile, date("Y-m-d H:i:s") . " ::: " . $_SERVER['REMOTE_ADDR'] . " ::: " 
            . $_SERVER['HTTP_USER_AGENT'] . " ::: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
}

if ($_GET['action'] == "openDir") {
    $pageContent = openTheDir($pageContent);
    print($pageContent);
    die();
}

if ( $_GET['action'] == "login" ) {
    if ( $_GET['u'] != "" && $_GET['p'] != "" ) {
        foreach ($users as $username=>$password) {
            if ( $_GET['u'] == $username && $_GET['p'] == $password ) {
                $_SESSION['u'] = $username; 
                break;
            }
        }
        header("Location:{$_SERVER['PHP_SELF']}"); exit();
    }
}

if ( isset($_SESSION['u']) && strlen($_SESSION['u']) > 0 ) {
    $sessid = $_SESSION['u'];
}

if ( $_GET['action'] == "downloadAlbum" && $_GET['dir'] != "" ) {
    if ( is_dir($defaultMp3Dir . "/" . $_GET['dir']) ) {
        $theDir = preg_replace("/^.+\/(.+)$/i", "\${1}", $_GET['dir']);
        $curdir = getcwd();
        chdir($defaultMp3Dir . "/" . $_GET['dir']);
        chdir("..");
        if ( !file_exists("{$tmpDir}/downloadAlbum") ) {
            mkdir("{$tmpDir}/downloadAlbum");
        }
        $md5 = md5(date("Y-m-dH:i:s") . microtime() . rand(0,999));
        mkdir("{$tmpDir}/downloadAlbum/{$md5}");
        exec("cp -Rf \"{$theDir}\" \"{$tmpDir}/downloadAlbum/{$md5}/{$theDir}\"");
        chdir("{$tmpDir}/downloadAlbum/{$md5}");
        exec("zip -r \"{$theDir}.zip\" \"{$theDir}\"");
        header('Content-Description: Download file');
        header("Content-type: applications/x-download");
        header("Content-Length: " . filesize("{$theDir}.zip"));
        header("Content-Disposition: attachment; filename=" . basename("{$theDir}.zip"));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
        readfile("{$theDir}.zip");
        exec("rm -Rf {$tmpDir}/downloadAlbum/{$md5}");
        chdir($curdir);
        die();
    }
} else if ($_GET['action'] == "play" && file_exists($defaultMp3Dir . '/' . $_GET['file']) 
        && preg_match("/\.(m4a|mp3|ogg)$/i", $_GET['file'])) {
} else if ( $_GET['action'] == "clearPersonal" ) {
    if ( file_exists("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json") ) {
        unlink("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json");
    }
} else if ($_GET['action'] == "createPlaylistJs" && file_exists($defaultMp3Dir . '/' . $_GET['dir']) 
        && is_dir($defaultMp3Dir . '/' . $_GET['dir'])) {
    $curdir = getcwd();
    chdir($defaultMp3Dir . '/' . $_GET['dir']);
    $a_files = glob("*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE);
    $fileName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $_GET['dir']);
    $filename = preg_replace("/__+/", "_", $filename);
    chdir($streamsDir);
    file_put_contents("{$fileName}.m3u", "");
    $m3uPlayer .= "<div class='m3uplayer'><table>";

    // THIS
    $adir = explode("/", $_GET['dir']);
    foreach ($adir as $adk=>$adv) {
        $adir[$adk] = rawurlencode($adv);
    }
    $tdir = implode("/", $adir);
    foreach ($a_files as $k=>$mp3) {
        $enc_file = htmlspecialchars($_GET['dir'] . '/' . $mp3);

        $amp3 = rawurlencode($mp3);
        $directMusicUrl = "{$defaultMp3Url}/{$tdir}/{$amp3}";
        $js_directMusicUrl = "{$defaultMp3Url}/{$tdir}/{$amp3}";

        $js_mp3 = preg_replace("/'/", "\\\'", $mp3);
        file_put_contents("{$fileName}.m3u", "{$directMusicUrl}\n", FILE_APPEND);
        $playlist .= "{'file':'{$js_directMusicUrl}', 'title':'{$js_mp3}'},";
    }

    file_put_contents("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json", $playlist, FILE_APPEND);
    $flashPlayer = <<<eof
<script type="text/javascript">
    jwplayer('mediaplayer').setup({
        'flashplayer': 'js/mediaplayer-5.7/player.swf',
        'id': 'playlistmediaplayer',
        'width': '480',
        //'height': '250',
        'height': '640',
        'backcolor': '#ffffff',
        'frontcolor': '#6d6d6d',
        'lightcolor': 'black',
        'screencolor': '#6d6d6d',
        'controlbar': 'top',
        'playlist': [{$playlist}],
        'repeat': 'always',
        'playlist.position': 'bottom',
        //'playlist.size': '250',
        'playlist.size': '640',
        'autoplay': true,
        'events': {
            onPlaylist: function(e) {
                var currentSong = jwplayer('mediaplayer').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
                $("div#currentlyPlaying").html(currentSong);
                setCurrentItem();
            },
            onPlaylistItem: function(e) {
                var currentSong = jwplayer('mediaplayer').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
                $("div#currentlyPlaying").html(currentSong);
                jwplayer('mediaplayer').getPlaylistItem().title;
                pauseme();
                setCurrentItem();
            },
            onPlay: function(e) {
                pauseme();
                setCurrentItem();
            },
            onPause: function(e) {
                startme();
                setCurrentItem();
            }
        }
    });

    function startme() {
        $("#playbutton").html("Play");
        $("#backbutton").remove();
        $("#nextbutton").remove();
        //$("#shufflebutton").remove();
    }

    function pauseme() {
        $("#playbutton").html("Pause");
        if ($("#backbutton").size() < 1) {
            $("#playbutton").after(" <span style='cursor:pointer;' id='backbutton' onclick='goback()' class='button'>Back</span>");
        }
        if ($("#nextbutton").size() < 1) {
            $("#backbutton").after(" <span style='cursor:pointer;' id='nextbutton' onclick='forward()' class='button'>Next</span>");
        }
        /*
        if ($("#shufflebutton").size() < 1) {
            $("#nextbutton").after(" <span style='cursor:pointer;' id='shufflebutton' onclick='shufflePlaylist()' class='button'>Shuffle</span>");  
        }
        */
    }

    function shufflePlaylist() {
        // This document id is the id when you call jwplayer('mediaplayer').setup().
        // mediaplayer is that ID.
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();

        if ( playlist.length > 0 ) {
            //...shuffle playlist
            for(var rnd, tmp, i = playlist.length; i; rnd = parseInt(Math.random()*i), tmp = playlist[--i], playlist[i] = playlist[rnd], playlist[rnd] = tmp);
          
            //...load the shuffled playlist
            player.sendEvent('LOAD', playlist);


            //...optional - to start playing after a shuffle
            //player.sendEvent('PLAY', 'true');
        }
    }

    function forward() {
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();

        if ( playlist.length > 0 ) {
            player.sendEvent('NEXT', 'true');
            setCurrentItem();
        }
    }

    function goback() {
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();

        if ( playlist.length > 0 ) {
            player.sendEvent('PREV', 'true');
            setCurrentItem();
        }
    }

    function setCurrentItem() {
        $("li.mp3").each(function(i, item){
            var html = $(item).children("span.text").first().children("a").first().html();
            var currentSong = jwplayer('mediaplayer').getPlaylistItem().title;
            if (html == currentSong) {
                $(item).css("font-weight", "bold");
            } else {
                $(item).css("font-weight", "normal");
            }
        });
    }
</script>
eof;
    $m3uPlayer .= "<tr><td class=\"currentsong\">Current song: <span id=\"currentSong\"></span> &#160;&#160;&#160; "
            . "<a style=\"cursor:pointer; text-decoration:underline;\" onclick=\"shufflePlaylist()\">shuffle</a></td></tr>";
    $m3uPlayer .= "<tr><td id=\"mediaplayer\"></td></tr>";
    $m3uPlayer .= "</table>";
    $m3uPlayer .= $flashPlayer;

    chdir($curdir);

    $esc_dir = preg_quote($_GET['dir']);

    print("<span id=\"theurl\" data-url=\"{$esc_dir}\" />{$m3uPlayer}</span>");
    die();
} else if ($_GET['action'] == "download") {
    $filename = preg_replace("/^.*\/(.*)$/i", "$1", $_GET['file']);
    header("Content-type: applications/x-download");
    header("Content-Length: " . filesize($defaultMp3Dir . '/' . $_GET['file']));
    header("Content-Disposition: attachment; filename=" . basename($filename));
    readfile($defaultMp3Dir . '/' . $_GET['file']);
    die();
}

function getFileIndex ($dir) {
    $curdir = getcwd();
    if ($dir === $GLOBALS['defaultMp3Dir']) {
        chdir($GLOBALS['defaultMp3Dir']);
        $dirLink = "";
    } else {
        if (!file_exists($GLOBALS['defaultMp3Dir'] . '/' . $dir)) {
            return false;
        }
        chdir($GLOBALS['defaultMp3Dir'] . '/' . $dir);
        $_SESSION['currentDir'] = $dir;
        $dirLink = "{$dir}/";
    }
    $a_files = glob("*");
    $index = "";
    $isMp3 = false;
    foreach ($a_files as $k=>$file) {
        if (is_dir($file)) {
            $html_data_url = preg_replace("/\"/", "\\\"", $dirLink . $file);
            if (file_exists("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}/small_montage.jpg")) {
                $background_url = "{$GLOBALS['defaultMp3Url']}/{$dirLink}{$file}/small_montage.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
                $index .= "<li class=\"dirlink-cover dirlinkcover\" style=\""
                        . "background:url('{$js_background_url}') "
                        . "no-repeat left center; background-size:128px 128px;\" data-url=\"" . $html_data_url 
                        . "\"><a style=\"padding-left:148px;\">" . htmlspecialchars($file) . "</a></li>";
            } else if (file_exists("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}/small_cover.jpg")) {
                $background_url = "{$GLOBALS['defaultMp3Url']}/{$dirLink}{$file}/small_cover.jpg";
                $js_background_url = preg_replace("/'/", "\\'", $background_url);
                $index .= "<li class=\"dirlink-cover dirlinkcover\" style=\""
                        . "background:url('{$js_background_url}') "
                        . "no-repeat left center; background-size:128px 128px;\" data-url=\"" . $html_data_url 
                        . "\"><a style=\"padding-left:148px;\">" . htmlspecialchars($file) . "</a></li>";
            } else {
                $index .= "<li class=\"dirlink-cover dirlinkcover\" style=\""
                        . "background:url('images/bigfolder.png') no-repeat left center; background-size:128px 128px;\" data-url=\"" 
                        . $html_data_url . "\"><a style=\"padding-left:148px;\">" . htmlspecialchars($file) . "</a></li>";
            }
        } else {
            if (preg_match("/\.(m4a|mp3|ogg|flac)$/i", $file)) {
                $isMp3 = true;
                $filesize = human_filesize($file);
                $displayFile = $file;

                $index .= "<li class='mp3'><span class=\"text\"><a target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></span></li>";
                continue;
            }
        }
    }
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
            $a_dir[$k] = "<span class='filesize_type'>{$backDir}</span>";
        } else if ($cnt === ($dirCnt - 1)) {
            // Have drop-down of all available directories under this directory.
            $thelinks = getDropDownAlbums($url);
            if ( $thelinks ) {
                $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\">{$backDir}<div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
            } else {
                $a_dir[$k] = "<span class='filesize_type'>{$backDir}</span><!--span.filesize_type-->";
            }
        } else {
            // Have drop-down of all available directories under this directory.
            $thelinks = getDropDownAlbums($url);
            $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a class=\"dirlink\" data-url=\"{$url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
        }
        $cnt++;
    }
    $backDirs = implode(" &rsaquo;<!--&raquo;--> ", $a_dir);

    $createPlaylistLink = "";
    if ($isMp3) {
        $createPlaylistLink = "<a id=\"playbutton\" class=\"button\" style='cursor:pointer;' data-url=\"" . $dir . "\">Play</a>";
    }

    if (preg_match("/\//", $dir)) {
        $previousDir = preg_replace("/^(.+)\/(.*)$/", "$1", $dir);
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0 ) {
            $previousDirListItem .= "<li class='previousDirectoryListItem'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else if ($dir != "") {
        $previousDir = $dir;
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0 ) {
            $previousDirListItem .= "<li class='previousDirectoryListItem'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
    } else {
        $previousDir = "";
        $previousDirListItem = "";
    }

    // This sets the class previousDirectoryListItem so that there's space under the Home link when no previous directory is listed.
    // When there is a previos directory, that class is applied to that list item.
    if ( $previousDirListItem == "" ) {
        $css_style = "class='previousDirectoryListItem'";
    } else {
        $css_style = "";
    }

    $searchBox = buildSearchBox();

    $index = "{$searchBox}<ul><li {$css_style}><img src=\"images/folder.png\" alt=\"folder\" /> <a class=\"dirlink\" data-url=\"\">Home</a></li>{$previousDirListItem}" . $index . "</ul>";

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
            $thelinks .= "<a class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$GLOBALS['defaultMp3Url']}/{$url}/{$html_thisdir}/small_montage.jpg\" alt=\"img\" /> {$html_thisdir}</a>"; 
        } else if (file_exists("{$thisdir}/small_cover.jpg")) {
            $thelinks .= "<a class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"{$GLOBALS['defaultMp3Url']}/{$url}/{$html_thisdir}/small_cover.jpg\" alt=\"img\" /> {$html_thisdir}</a>"; 
        } else {
            $thelinks .= "<a class=\"droplink\" data-url=\"{$enc_html_thisdir}\"><img class=\"dropimg\" src=\"images/bigfolder.png\" alt=\"img\" /> {$html_thisdir}</a>"; 
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

$streams = "";
if ( $_GET['action'] == "wfpklivestream" ) {
    $streams .= <<<eof
Current song: <span id="currentSong"></span><br />
<div id="wfpklivestream"></div>
<script type="text/javascript">
    jwplayer('wfpklivestream').setup({
        'flashplayer': 'js/mediaplayer-5.7/player.swf',
        'id': 'playlistmediaplayer',
        'width': '600',
        'height': '250',
        'controlbar': 'top',
        'playlist': [{ 'file':'http://lpm.streamguys.tv:80/lpm128-3', 'title':'WFPK live stream' }],
        'repeat': 'always',
        'playlist.position': 'bottom',
        'playlist.size': '250',
        'events': {
            onPlaylist: function(e) {
                var currentSong = jwplayer('wfpklivestream').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            },
            onPlaylistItem: function(e) {
                var currentSong = jwplayer('wfpklivestream').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            }
        }
    });
</script>
eof;
}

if ($_GET['dir'] == "" || !file_exists($defaultMp3Dir . '/' . $_GET['dir']) || !is_dir($defaultMp3Dir . '/' . $_GET['dir']) || preg_match("/\.\./", $_GET['dir'])) {
    $curdir = getcwd();
    chdir($streamsDir);
    $a_streams = glob("*.m3u");
    chdir($curdir);
    if ( file_exists("TURNTHISFEATUREOFFplaylists/personal_playlist.{$sessid}.json") ) {
        $streams .= "<h3>Personal Stream</h3><ul>";
        $streams .= "<li><img src=\"images/stream.png\" alt=\"stream\" /><a href=\"{$streamsRootDirUrl}/index.php?action=clearPersonal\" onclick=\"return confirm('Are you sure you want to clear this playlist?')\">clear playlist</a> &#160;&#160;&#160; <a style=\"cursor:pointer; text-decoration:underline;\" onclick=\"shufflePlaylist()\">shuffle</a></li></ul>";
        $playlist = file_get_contents("playlists/personal_playlist.{$sessid}.json");
        $playlist = preg_replace("/(\r|\n)/", "", $playlist);
        // you must remove this, because i don't when appending to the file.
        $playlist = rtrim($playlist, ",");
        $streams .= <<<eof
Current song: <span id="currentSong"></span><br />
<div id="personal"></div>
<script type="text/javascript">
    jwplayer('personal').setup({
        'flashplayer': 'js/mediaplayer-5.7/player.swf',
        'id': 'playlistmediaplayer',
        'width': '480',
        'height': '640',
        'backcolor': '#ffffff',
        'frontcolor': '#6d6d6d',
        'lightcolor': 'black',
        'screencolor': '#6d6d6d',
        'controlbar': 'top',
        'playlist': [{$playlist}],
        'repeat': 'always',
        'playlist.position': 'bottom',
        'playlist.size': '640',
        'events': {
            onPlaylist: function(e) {
                var currentSong = jwplayer('personal').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            },
            onPlaylistItem: function(e) {
                var currentSong = jwplayer('personal').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            }
        }
    });

    function shufflePlaylist() {
        // This document id is the id when you call jwplayer('mediaplayer').setup().
        // mediaplayer is that ID.
        var player = document.getElementById("personal");
        var playlist = player.getPlaylist();

        if ( playlist.length > 0 ) {
            //...shuffle playlist
            for(var rnd, tmp, i = playlist.length; i; rnd = parseInt(Math.random()*i), tmp = playlist[--i], playlist[i] = playlist[rnd], playlist[rnd] = tmp);
          
            //...load the shuffled playlist
            player.sendEvent('LOAD', playlist);


            //...optional - to start playing after a shuffle
            //player.sendEvent('PLAY', 'true');
        }
    }
</script>
eof;
    }
    $pageContent = getFileIndex($defaultMp3Dir);
} else {
    $pageContent .= openTheDir($_GET['dir']);
}

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$viewport = "";
if ( preg_match("/Android/i", $_SERVER['HTTP_USER_AGENT']) ) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
}

$r = rand(0, 255);
$g = ($r + 16) % 256;
$b = ($r + 16) % 256;
$color = "rgb($r, $g, $b)";
$color = "#362F20";

$html = <<<eof
<html>
<head>
{$viewport}
<script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="js/mediaplayer-5.7/jwplayer.js"></script>
<title>MP3 Stream Builder</title>
<style type="text/css">
body {
    font-family:sans-serif;
    font-size:100%;
    margin:0;
    padding:0;
    background-color:white;
    color:#6d6d6d;
    border-top:4px solid #3181b7;
}

.mp3 {
    margin-bottom:4px;
}

.mp3img {
}

.small-text {
    font-size:0.50em;
}

div#container {
    margin:32px;
    background-color:white;
}

#content {
    margin-right:32px;
    width:500px;
    min-width:500px;
    float:left;
}

#content-player {
    width:500px;
    min-width:500px;
    float:left;
}

h1, h2, h3, h4, h5, p {
    margin:4px;
    padding:4px;
}

.filesize_type {
    font-size:.85em;
    color:gray;
}

.message {
    margin:0;
    padding:0;
    color:#404040;
}

a, a:link {
    color:{$color};
    text-decoration:none;
}

a:hover {
    color:gray;
    text-decoration:underline;
}

ul {
    margin:0;
    padding:0;
}

li {
    list-style-type:none;
}
.dirlink, .dirlinkcover {
    cursor:pointer;
    margin-bottom:16px; 
}
.dirlinkcover {
    font-size:.75em;
    -moz-box-shadow:0px 0px 4px #ececec;
    -webkit-box-shadow:0px 0px 4px #ececec;
    -o-box-shadow:0px 0px 4px #ececec;
    box-shadow:0px 0px 4px #ececec;
}
.dirlinkcover:hover {
    -moz-box-shadow:0px 0px 4px #3181b7;
    -webkit-box-shadow:0px 0px 4px #3181b7;
    -o-box-shadow:0px 0px 4px #3181b7;
    box-shadow:0px 0px 4px #3181b7;
    font-weight:bold;
}

.mp3 {
    margin-bottom:8px;
    font-size:0.9em;
}

#currentSong {
    font-weight:bold;
}

.previousDirectoryListItem {
    margin-bottom:16px;
}

div.coverart img {
    float:right;
    max-width:175px;
    margin:16px;
    -moz-box-shadow:3px 3px 0px silver;
    -webkit-box-shadow:3px 3px 0px silver;
    -o-box-shadow:3px 3px 0px silver;
    box-shadow:3px 3px 0px silver;
}

.clear {
	margin:0;
	padding:0;
	width:0;
	height:0;
	clear:both;
}

a.button, .button {
    display:inline-block;
    font-family:"helvetica neue", helvetica, arial, freesans, "liberation sans", "numbus sans l", sans-serif;
    font-size:13px;
    text-decoration:none;
    text-align:center;
    padding:4px 8px;
    white-space:nowrap;
    color:white;
    font-weight:bold;
    background-color:#3181b7;
    -moz-box-shadow:3px 3px 0px silver;
    -webkit-box-shadow:3px 3px 0px silver;
    -o-box-shadow:3px 3px 0px silver;
    box-shadow:3px 3px 0px silver;
    text-shadow: 1px 1px 2px gray; 

    /*
    background: #3181b7;
    background: -moz-linear-gradient(top,  #3181b7 0%, #528bb2 50%, #3181b7 51%, #4688b5 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#3181b7), color-stop(50%,#528bb2), color-stop(51%,#3181b7), color-stop(100%,#4688b5));
    background: -webkit-linear-gradient(top,  #3181b7 0%,#528bb2 50%,#3181b7 51%,#4688b5 100%);
    background: -o-linear-gradient(top,  #3181b7 0%,#528bb2 50%,#3181b7 51%,#4688b5 100%);
    background: -ms-linear-gradient(top,  #3181b7 0%,#528bb2 50%,#3181b7 51%,#4688b5 100%);
    background: linear-gradient(to bottom,  #3181b7 0%,#528bb2 50%,#3181b7 51%,#4688b5 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#3181b7', endColorstr='#4688b5',GradientType=0 );
    */

}

a.button:hover, .button:hover {
    background-color:#d88900;
}

a.button:active, .button:active {
    background-color:#ffb22d;
}

a.button:active, a.button:focus, .button:active, .button:focus {
}

span.dropwrapper {
    position:relative;
}

div.drop {
    display:none;
    max-height:480px;
    width:480px;
    max-width:640px;
    wrap:auto;
    z-index:10;
}

.droplink {
    display:block;
    padding:4px;
    text-decoration:none;
    cursor:pointer;
}

.dropimg {
    width:64px;
    height:64px;
    margin-right:4px;
}

.droplink:hover {
    color:#404040;
    background-color:white;
    text-decoration:none;
}

span.dropwrapper:hover div.drop {
    display:block;
    position:absolute;
    top:1em;
    left:0;
    /* extra fancying style */
    overflow:auto;
    background-color:#EBEBEB;
    margin:0px;
    padding:0px;
    /* the topleft corner is still a hard right angle */
    -moz-box-shadow:3px 3px 0px silver;
    -webkit-box-shadow:3px 3px 0px silver;
    -o-box-shadow:3px 3px 0px silver;
    box-shadow:3px 3px 0px silver;
    opacity:0.9;
    -ms-filter:'alpha(opacity=90)';
    filter:alpha(opacity=90);
}

.m3uplayer td, .m3uplayer tr, .m3uplayer table, .m3uplayer tbody, .m3uplayer thead {
    border:0;
    background-color:white;
}

.currentsong {

}
table {
    border-collapse:collapse;
    margin:0;
    padding:4px;
    background-color:white;
}

tr td:first-child {
    background-color:white;
    margin:0;
    padding:0;
    font-size:0.8em;
    color:#6d6d6d;
}

.m3uplayer {
    margin:0;
    padding:0;
}

.small-cover {
    width:128px;
    height:128px;
}

.dirlink-cover a {
    line-height:128px;
    height:128px;
}

.dirlink-cover a img {
    -moz-box-shadow:3px 3px 0px silver;
    -webkit-box-shadow:3px 3px 0px silver;
    -o-box-shadow:3px 3px 0px silver;
    box-shadow:3px 3px 0px silver;
}

#searchbox {
}

#search {
    width:50%;
    margin-bottom:8px;
    padding:4px;
    border:1px solid gray;
    -moz-box-shadow:3px 3px 0px silver;
    -webkit-box-shadow:3px 3px 0px silver;
    -o-box-shadow:3px 3px 0px silver;
    box-shadow:3px 3px 0px silver;
}
</style>
<script type="text/javascript">
function toggleMusicOn(url) {
    if ($(".m3uplayer").size() > 0 && url == $("#theurl").data("url")) {
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();
        if (playlist.length > 0) {
            if ($("#playbutton").html() == "Play") {
                player.sendEvent('PLAY', 'true');
                $("#playbutton").html("Pause");
            } else {
                player.sendEvent('PLAY', 'false');
                $("#playbutton").html("Play");
            }
        }
    } else {
        //location.href = "index.php?action=createPlaylist&dir=" + encodeURIComponent(url);
        createPlaylistJs(url);
    }
}

function createPlaylistJs(url) {
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=createPlaylistJs&dir=" + encodeURIComponent(url),
        success: function(html){
            $("#content-player").html(html);
        }
    });
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    //location.href = url;
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=openDir&url=" + encodeURIComponent(url) + "&dir=" + encodeURIComponent(url),
        success: function(text){
            $("#content").html(text);
        }
    });
}

function init() {
    var hash = window.location.hash;
    hash = hash.replace(/^#/, "");
    var hashVars = hash.split("/");
    switch(hashVars[1]) {
        case "open":
            var dir = decodeURIComponent(hashVars[2]);
            openDir(dir);
            break;
        default:
            var doNothing = true;
    }
}

function search(q) {
    $("#test").html(q);
}

$(document).ready(function(){
    if ($("#test").size() < 1) {
        $("body").append("Coming Soon: <div id=\"test\"></div>");
    }

    init();

    if ($("#content-player").size() > 0 && $(".m3uplayer").size() > 0) {
        $("#playbutton").html("Pause");
    }
    
    $("#playbutton").live("click", function() {
        toggleMusicOn($(this).data('url'));
    });

    $(".droplink").live("click", function() {
        openDir($(this).data('url'));
    });

    $(".dirlink").live("click", function() {
        openDir($(this).data('url'));
    });

    $(".dirlinkcover").live("click", function() {
        openDir($(this).data('url'));
    });

    $("#search").live("keyup", function() {
        search($(this).val());
    });
});
</script>
</head>
<body>
    <div id="container">
        <div id="content">
            {$pageContent}
        </div><!--div#content-->
        <div id="content-player">
            {$message}
        </div><!--div#content-player-->
        <div class="clear"></div>
    </div><!--div#container-->
</body>
</html>
eof;

/**
 * Actually print the xHTML
 */
ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();

