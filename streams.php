<?php
session_start();
$sessid = session_id();
require_once("WsPhpLibrary.php");

$webroot = "/var/www/nas";
$defaultMp3Dir = "{$webroot}/music.wjsams.com/htdocs/music";
$defaultMp3Url = "https://music.wjsams.com/music";
$streamsRootDir = "{$webroot}/music.wjsams.com/htdocs/streams";
$streamsRootDirUrl = "https://music.wjsams.com/streams";
$streamsDir = "{$webroot}/music.wjsams.com/htdocs/streams/m3u";
$streamsUrl = "https://music.wjsams.com/streams/m3u";
$tmpDir = "/tmp";

// This is a really cheap login scheme for storing playlists.
// Since this is a personal player you can manually add people
// here. Use whatever you want. You just need to store a username
// in the $_SESSION['u'] variable.
if ( $_GET['action'] == "login" ) {
    if ( $_GET['u'] != "" && $_GET['p'] != "" ) {
        if ( $_GET['u'] == "someUserName" && $_GET['p'] == "yourPassword" ) {
            $_SESSION['u'] = $_GET['u']; 
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
} else if ($_GET['action'] == "play" && file_exists($defaultMp3Dir . '/' . $_GET['file']) && preg_match("/\.mp3$/i", $_GET['file'])) {
    //print("Going to play {$_GET['file']}<br />");
} else if ( $_GET['action'] == "clearPersonal" ) {
    if ( file_exists("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json") ) {
        unlink("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json");
    }
} else if ($_GET['action'] == "createPlaylist" && file_exists($defaultMp3Dir . '/' . $_GET['dir']) && is_dir($defaultMp3Dir . '/' . $_GET['dir'])) {
    $curdir = getcwd();
    chdir($defaultMp3Dir . '/' . $_GET['dir']);
    $a_files = glob("*.mp3");
    $fileName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $_GET['dir']);
    $filename = preg_replace("/__+/", "_", $filename);
    chdir($streamsDir);
    file_put_contents("{$fileName}.m3u", "");
    $m3uPlayer = "<style type='text/css'>table {border-collapse:collapse; border:1px solid silver; margin:0; padding:4px;} tr td:first-child {background-color:white; margin:0; padding:0 8px 0 8px;} div.m3uplayer {margin:0; padding:16px;}</style>";
    $m3uPlayer .= "<div class='m3uplayer'><table>";

    foreach ($a_files as $k=>$mp3) {
        $enc_file = htmlspecialchars($_GET['dir'] . '/' . $mp3);
        $directMusicUrl = preg_replace("/ /", "%20", "{$defaultMp3Url}/{$_GET['dir']}/{$mp3}");
        $js_directMusicUrl = preg_replace("/'/", "\\\'", $directMusicUrl);
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
        'width': '600',
        'height': '250',
        'controlbar': 'top',
        'playlist': [{$playlist}],
        'repeat': 'always',
        'playlist.position': 'bottom',
        'playlist.size': '250',
        'events': {
            onPlaylist: function(e) {
                var currentSong = jwplayer('mediaplayer').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            },
            onPlaylistItem: function(e) {
                var currentSong = jwplayer('mediaplayer').getPlaylistItem().title;
                $("span#currentSong").html(currentSong);
            }
        }    });

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
</script>
eof;
    $m3uPlayer .= "<tr><td style=\"padding:4px;\">Current song: <span id=\"currentSong\"></span> &#160;&#160;&#160; <a style=\"cursor:pointer; text-decoration:underline;\" onclick=\"shufflePlaylist()\">shuffle</a></td></tr>";
    $m3uPlayer .= "<tr><td id=\"mediaplayer\"></td></tr>";
    $m3uPlayer .= "</table>";
    $m3uPlayer .= $flashPlayer;

    chdir($curdir);
    $_SESSION['message'] = "Your playlist has been created.<br /><a href=\"{$streamsUrl}/{$fileName}.m3u\">{$streamsUrl}/{$fileName}.m3u</a><br /><br />A list of all playlists can be found at the bottom of the <a href=\"{$_SERVER['PHP_SELF']}\">Home</a> page.<br />{$m3uPlayer}";
    header("Location:{$_SERVER['PHP_SELF']}?dir=" . urlencode($_GET['dir']));
    die();
} else if ($_GET['action'] == "download") {
    $filename = preg_replace("/^.*\/(.*)$/i", "$1", $_GET['file']);
    //header('Content-Description: Download file');
    header("Content-type: applications/x-download");
    header("Content-Length: " . filesize($defaultMp3Dir . '/' . $_GET['file']));
    header("Content-Disposition: attachment; filename=" . basename($filename));
    //header("Content-Transfer-Encoding: binary");
    //header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    //header('Expires: 0');
    //header('Pragma: public');
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
            $index .= "<li><img src=\"images/folder.png\" alt=\"folder\" /> <a href=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($file) . "</a></li>";
        } else {
            if (preg_match("/\.mp3$/i", $file)) {
                $isMp3 = true;
                $filesize = human_filesize($file);
                $displayFile = preg_replace("/\.mp3$/i", "", $file);

        // allowed id3 versions
        //$allowedid3s[]=0; // ID3_BEST
        //$allowedid3s[]=1;   // ID3_V1_0
        //$allowedid3s[]=3;   // ID3_V1_1
        //$allowedid3s[]=4;   // ID3_V2_1
        //$allowedid3s[]=12;    // ID3_V2_2
        //$allowedid3s[]=28;    // ID3_V2_3
        //$allowedid3s[]=60;    // ID3_V2_4

        //$allowedid3s[]=31;  // UNKNOWN?
        //$id3_version = id3_get_version($file);

		// start: requires Id3 tag support
		//if ( in_array($id3_version, $allowedid3s) ) {
			// start: doesn't require Id3 tag support
			$index .= "<li><img src=\"images/mp3.png\" alt=\"mp3\" /> <a href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></li>";
			continue;
			// end: doesn't require Id3 tag support
        //} else if ( !@id3_get_tag($file) ) {
			// start: doesn't require Id3 tag support
			$index .= "<li><img src=\"images/mp3.png\" alt=\"mp3\" /> <a href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></li>";
			continue;
			// end: doesn't require Id3 tag support
		//}
        //$mp3Id3 = @id3_get_tag($file);

		// Analyze file and store returned data in $ThisFileInfo
		//$a_getid3 = $getID3->analyze($file);

		// end: requires Id3 tag support

		//print("<pre>");var_dump($mp3Id3);print("</pre>");
		// If you don't have the Id3 tag support installed, used this $index .= line instead
    		// of the $mp3Id3 above and the esc_title stuff below.


		// start: requires Id3 tag support
        /*
		$esc_title = htmlspecialchars($mp3Id3['title']);
		if ( $esc_title == "" ) {
		    $esc_title = htmlspecialchars($file);
		}
		$esc_track = htmlspecialchars($mp3Id3['track']);
                $index .= "<li><img src=\"images/mp3.png\" alt=\"mp3\" /> <a href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">{$esc_track} - {$esc_title}</a> <code>{$filesize}</code></li>";
        */
		// end: requires Id3 tag support
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
            $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a href=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir={$enc_url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
        }
        $cnt++;
    }
    $backDirs = implode(" / ", $a_dir);

    $createPlaylistLink = "";
    if ($isMp3) {
        $createPlaylistLink = "<a class=\"button\" href=\"{$_SERVER['PHP_SELF']}?action=createPlaylist&amp;dir=" . urlencode($dir) . "\">Play</a>";
    }

    if (preg_match("/\//", $dir)) {
        $previousDir = preg_replace("/^(.+)\/(.*)$/", "$1", $dir);
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /><!-- <a href=\"{$_SERVER['PHP_SELF']}?action=openIndex&dir=" . urlencode($previousDir) . "\">Previous directory</a> &#160;&#160;&#160;--> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{mp3,MP3}", GLOB_BRACE)) > 0 ) {
            $previousDirListItem .= "<li class='previousDirectoryListItem'>{$createPlaylistLink} <a class=\"button download\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\">Download</a></li>";
        }
    } else if ($dir != "") {
        $previousDir = $dir;
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /><!-- <span class='filesize_type'><a href=\"{$_SERVER['PHP_SELF']}\">Previous directory</a></span> &#160;&#160;&#160;--> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{mp3,MP3}", GLOB_BRACE)) > 0 ) {
            $previousDirListItem .= "<li class='previousDirectoryListItem'>{$createPlaylistLink} <a class=\"button download\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\">Download</a></li>";
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
    $index = "<ul><li {$css_style}><img src=\"images/folder.png\" alt=\"folder\" /> <a href=\"{$_SERVER['PHP_SELF']}\">Home</a></li>{$previousDirListItem}" . $index . "</ul>";

    return $index;
}

function getDropDownAlbums($url) {
    $curdir = getcwd();
    chdir("{$GLOBALS['defaultMp3Dir']}/{$url}");
    $a_available_dirs = glob("*", GLOB_ONLYDIR);
    $thelinks = "";
    foreach ($a_available_dirs as $k5=>$thisdir) {
        $enc_thisdir = urlencode($url . "/" . $thisdir);
        $html_thisdir = htmlspecialchars($thisdir);
        $thelinks .= "<a class=\"droplink\" href=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir={$enc_thisdir}\">{$html_thisdir}</a>"; 
    }
    chdir($curdir);
    if ( $thelinks == "" ) {
        return false;
    }
    return $thelinks;
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
    if ( file_exists("playlists/personal_playlist.{$sessid}.json") ) {
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
        'width': '600',
        'height': '250',
        'controlbar': 'top',
        'playlist': [{$playlist}],
        'repeat': 'always',
        'playlist.position': 'bottom',
        'playlist.size': '250',
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
    $streams .= "<h3>Current Streams</h3><ul>";
    foreach ($a_streams as $k=>$stream) {
        $streams .= "<li><img src=\"images/stream.png\" alt=\"stream\" /> <a href=\"{$streamsUrl}/{$stream}\">{$stream}</a></li>";
    }
    $streams .= "</ul>";
    $pageContent = getFileIndex($defaultMp3Dir) . $streams;
} else {
    if ( file_exists($GLOBALS['defaultMp3Dir'] . "/" . $_GET['dir'] . "/cover.jpg") ) {
	$pageContent .= "<div class=\"coverart\"><a target=\"_blank\" href=\"../music/{$_GET['dir']}/cover.jpg\"><img src=\"../music/{$_GET['dir']}/cover.jpg\" alt=\"cover\" /></a></div><span class=\"clear\"></span>";
    }
    $pageContent .= getFileIndex($_GET['dir']);
}

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$viewport = "";
if ( preg_match("/Android/i", $_SERVER['HTTP_USER_AGENT']) ) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
}

$html = <<<eof
<html>
<head>
{$viewport}
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/mediaplayer-5.7/jwplayer.js"></script>
<title>MP3 Stream Builder</title>
<style type="text/css">
body {
    font-family:sans-serif;
    font-size:100%;
    margin:0;
    padding:0;
    background-color:silver;
    text-align:center;
}
div#container {
    text-align:left;
    margin:16px auto;
    padding:16px;
    border:3px solid #dfdfdf;
    background-color:white;
    width:640px;
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
    margin:4px;
    padding:4px;
    border:3px solid maroon;
    background-color:#ebebeb;
    color:#404040;
}
a, a:link {
    color:navy;
    text-decoration:none;
}
a:hover {
    color:gray;
    text-decoration:underline;
}
li {
    list-style-type:none;
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
    border:3px solid #dfdfdf;
    margin-left:16px;
    margin-bottom:16px;
}
.clear {
	margin:0;
	padding:0;
	width:0;
	height:0;
	clear:both;
}

.button {
  -moz-border-radius:      3px;
  -webkit-border-radius:   3px;
  border-radius:3px;
  background:              white url('button.png') 0 0 repeat-x; /* Image fallback */
  background:             -moz-linear-gradient(0% 170% 90deg, #c4c4c4, white);
  background:             -webkit-gradient(linear, 0% 0%, 0% 170%, from(white), to(#c4c4c4));
  border:                  1px solid;
  border-color:            #e6e6e6 #cccccc #cccccc #e6e6e6;
  border-radius:           3px;
  color:                   #404040;
  display:                 inline-block;
  font-family:            "helvetica neue", helvetica, arial, freesans, "liberation sans", "numbus sans l", sans-serif;
  font-size:               13px;
  outline                  0;
  padding:                 4px;
  text-align:              center;
  text-decoration:         none;
  text-shadow:             1px 1px 0 white; 
  white-space:             nowrap;
}

.button:hover {
    background:           -moz-linear-gradient(0% 170% 90deg, #b8b8b8, white);
    background:           -webkit-gradient(linear, 0% 0%, 0% 170%, from(white), to(#b8b8b8));
    border-color:          #99ccff;
    color:                 #333333;
    text-decoration:none;
}

.button:active {
    position:              relative;
    top:                   1px;
    text-decoration:none;
}

.button:active, .button:focus {
    background-position:   0 -25px;
    background:           -moz-linear-gradient(0% 170% 90deg, white, #dedede);
    background:           -webkit-gradient(linear, 0% 0%, 0% 170%, from(#dedede), to(white));
    border-color:          #8fc7ff #94c9ff #94c9ff #8fc7ff;
    color:                 #1a1a1a;
    text-shadow:           1px -1px 0 rgba(255, 255, 255, 0.5);
    text-decoration:none;
}

span.dropwrapper {
    position:relative;
}

div.drop {
    display:none;
    max-height:333px;
    width:256px;
    max-width:512px;
    wrap:auto;
}

a.droplink {
    display:block;
    padding:4px;
    text-decoration:none;
}

a.droplink:hover {
    color:#404040;
    background-color:white;
    text-decoration:none;
}

span.dropwrapper:hover div.drop {
    display:block;
    position:absolute;
    top:1.1em;
    left:0;
    /* extra fancying style */
    overflow:auto;
    background-color:#EBEBEB;
    border-right:1px solid #6d6e70;
    border-bottom:1px solid #6d6e70;
    border-left:1px solid #6d6e70;
    margin:0px;
    padding:0px;
    /* the topleft corner is still a hard right angle */
    /*
    -webkit-border-radius:8px;
    -moz-border-radius:8px;
    */
    -moz-box-shadow:4px 4px 4px silver;
    -webkit-box-shadow:4px 4px 4px silver;
    -o-box-shadow:4px 4px 4px silver;
    box-shadow:4px 4px 4px silver;
    opacity:0.9;
    -ms-filter:'alpha(opacity=90)';
    filter:alpha(opacity=90);
}
</style>
<script type="text/javascript">
$(document).ready(function(){

});
</script>
</head>
<body>
<div id="container">
<h1>MP3 Stream Builder</h1>
<div id="content">
{$message}
{$pageContent}
</div>
</div>
</body>
</html>
eof;
print($html);
