<?php
define("STREAMS", 1);

require_once("config.php");

//
// End Configuration
//

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

session_start();
$sessid = session_id();

require_once("WsPhpLibrary.php");

function openTheDir() {
    $pageContent = "";
    // This is when you open a dir.
    if (file_exists($GLOBALS['defaultMp3Dir'] . "/" . $_GET['dir'] . "/cover.jpg")) {
        $pageContent .= "<div class=\"coverart\"><a target=\"_blank\" href=\"../music/{$_GET['dir']}/cover.jpg\"><img src=\"../music/{$_GET['dir']}/cover.jpg\" alt=\"cover\" /></a></div><span class=\"clear\"></span>";
    }
    $pageContent .= getFileIndex($_GET['dir']);
    return $pageContent;
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
} else if ($_GET['action'] == "play" && file_exists($defaultMp3Dir . '/' . $_GET['file']) && preg_match("/\.(mp3|ogg)$/i", $_GET['file'])) {
    //print("Going to play {$_GET['file']}<br />");
} else if ( $_GET['action'] == "clearPersonal" ) {
    if ( file_exists("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json") ) {
        unlink("{$streamsRootDir}/playlists/personal_playlist.{$sessid}.json");
    }
} else if ($_GET['action'] == "createPlaylist" && file_exists($defaultMp3Dir . '/' . $_GET['dir']) && is_dir($defaultMp3Dir . '/' . $_GET['dir'])) {
    //function createPlaylist() 
    $curdir = getcwd();
    chdir($defaultMp3Dir . '/' . $_GET['dir']);
    $a_files = glob("*.{mp3,ogg,MP3,OGG}", GLOB_BRACE);
    $fileName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $_GET['dir']);
    $filename = preg_replace("/__+/", "_", $filename);
    chdir($streamsDir);
    file_put_contents("{$fileName}.m3u", "");
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
    }

    function pauseme() {
        $("#playbutton").html("Pause");
        if ($("#backbutton").size() < 1) {
            $("#playbutton").after(" <span style='cursor:pointer;' id='backbutton' onclick='goback()' class='button'>Back</span>");
        }
        if ($("#nextbutton").size() < 1) {
            $("#backbutton").after(" <span style='cursor:pointer;' id='nextbutton' onclick='forward()' class='button'>Next</span>");
        }
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
            if (html == "&nbsp;" + currentSong) {
                $(item).css("font-weight", "bold");
            } else {
                $(item).css("font-weight", "normal");
            }
        });
    }

</script>
eof;
    $m3uPlayer .= "<tr><td class=\"currentsong\">Current song: <span id=\"currentSong\"></span> &#160;&#160;&#160; <a style=\"cursor:pointer; text-decoration:underline;\" onclick=\"shufflePlaylist()\">shuffle</a></td></tr>";
    $m3uPlayer .= "<tr><td id=\"mediaplayer\"></td></tr>";
    $m3uPlayer .= "</table>";
    $m3uPlayer .= $flashPlayer;

    chdir($curdir);
    $esc_dir = preg_quote($_GET['dir']);
    $_SESSION['message'] = "<span id=\"theurl\" data-url=\"{$esc_dir}\" />{$m3uPlayer}<!--<br /><span class=\"small-text\"><a href=\"{$streamsUrl}/{$fileName}.m3u\">{$streamsUrl}/{$fileName}.m3u</a></span>-->";
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
            if (file_exists("{$GLOBALS['defaultMp3Dir']}/{$dirLink}{$file}/small_cover.jpg")) {
                $index .= "<li class=\"dirlink-cover dirlinkcover\" style=\"margin-bottom:4px; background:url('{$GLOBALS['defaultMp3Url']}/{$dirLink}{$file}/small_cover.jpg') no-repeat left center; background-size:128px 128px;\" data-url=\"" . $dirLink . $file . "\"><a style=\"padding-left:148px;\">" . htmlspecialchars($file) . "</a></li>";
            } else {
                $index .= "<li class=\"dirlink-cover dirlinkcover\" style=\"margin-bottom:4px; background:url('images/bigfolder.png') no-repeat left center; background-size:128px 128px;\" data-url=\"" . $dirLink . $file . "\"><a style=\"padding-left:148px;\">" . htmlspecialchars($file) . "</a></li>";
            }
        } else {
            if (preg_match("/\.(mp3|ogg)$/i", $file)) {
                $isMp3 = true;
                $filesize = human_filesize($file);
                //$displayFile = preg_replace("/\.mp3$/i", "", $file);
                $displayFile = $file;

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
			$index .= "<li class='mp3'><!--<img class=\"mp3img\" src=\"images/mp3.png\" alt=\"mp3\" />--><span class=\"text\"><a target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></span></li>";
			continue;
			// end: doesn't require Id3 tag support
        //} else if ( !@id3_get_tag($file) ) {
			// start: doesn't require Id3 tag support
			$index .= "<li class='mp3'><!--<img class=\"mp3img\" src=\"images/mp3.png\" alt=\"mp3\" />--><span class=\"text\"><a target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=download&amp;file=" . urlencode($dirLink . $file) . "\">" . htmlspecialchars($displayFile) . "</a> <code>{$filesize}</code></span></li>";
			continue;
			// end: doesn't require Id3 tag support
		//}
        //$mp3Id3 = @id3_get_tag($file);

		// Analyze file and store returned data in $ThisFileInfo
		//$a_getid3 = $getID3->analyze($file);

		// end: requires Id3 tag support

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
            //$a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a class=\"dirlink\" href=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir={$enc_url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
            $a_dir[$k] = "<span class='filesize_type'><span class=\"dropwrapper\"><a class=\"dirlink\" data-url=\"{$url}\">{$backDir}</a><div class=\"drop\">{$thelinks}</div><!--div.drop--></span><!--span.dropwrapper--></span><!--span.filesize_type-->";
        }
        $cnt++;
    }
    $backDirs = implode(" &rsaquo;<!--&raquo;--> ", $a_dir);

    $createPlaylistLink = "";
    if ($isMp3) {
        //$createPlaylistLink = "<a id=\"playbutton\" class=\"button\" onclick=\"location.href='{$_SERVER['PHP_SELF']}?action=createPlaylist&amp;dir=" . urlencode($dir) . "'\" style='cursor:pointer;'\">Play</a>";
        $createPlaylistLink = "<a id=\"playbutton\" class=\"button\" style='cursor:pointer;' data-url=\"" . $dir . "\">Play</a>";
    }

    if (preg_match("/\//", $dir)) {
        $previousDir = preg_replace("/^(.+)\/(.*)$/", "$1", $dir);
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /><!-- <a href=\"{$_SERVER['PHP_SELF']}?action=openIndex&dir=" . urlencode($previousDir) . "\">Previous directory</a> &#160;&#160;&#160;--> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0 ) {
            $previousDirListItem .= "<li class='previousDirectoryListItem'>{$createPlaylistLink} <a class=\"button download\" target=\"_blank\" href=\"{$_SERVER['PHP_SELF']}?action=downloadAlbum&amp;dir=" . urlencode($dir) . "\" onclick=\"return confirm('After clicking ok, it may take some time to prepare your download - please wait - your download will begin shortly.')\">Download</a></li>";
        }
        //$previousDirListItem .= "<div id=\"currentlyPlaying\"></div>";
    } else if ($dir != "") {
        $previousDir = $dir;
        $previousDirListItem = "<li class='previousDirectoryListItem'><img src=\"images/folder.png\" alt=\"folder\" /><!-- <span class='filesize_type'><a href=\"{$_SERVER['PHP_SELF']}\">Previous directory</a></span> &#160;&#160;&#160;--> {$backDirs}</li>";
        if ( count(glob("{$GLOBALS['defaultMp3Dir']}/{$dir}/*.{mp3,MP3,ogg,OGG}", GLOB_BRACE)) > 0 ) {
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
    $index = "<ul><li {$css_style}><img src=\"images/folder.png\" alt=\"folder\" /> <a class=\"dirlink\" data-url=\"\">Home</a></li>{$previousDirListItem}" . $index . "</ul>";


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
        //print("{$GLOBALS['defaultMp3Dir']}/{$thisdir}/small_cover.jpg");
        if (file_exists("{$thisdir}/small_cover.jpg")) {
            $thelinks .= "<a class=\"droplink\" data-url=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir={$enc_thisdir}\"><img class=\"dropimg\" src=\"{$GLOBALS['defaultMp3Url']}/{$url}/{$html_thisdir}/small_cover.jpg\" alt=\"img\" /> {$html_thisdir}</a>"; 
        } else {
            $thelinks .= "<a class=\"droplink\" data-url=\"{$_SERVER['PHP_SELF']}?action=openIndex&amp;dir={$enc_thisdir}\">{$html_thisdir}</a>"; 
        }
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
    $streams .= "<h3>Current Streams</h3><ul>";
    foreach ($a_streams as $k=>$stream) {
        $streams .= "<li><img src=\"images/stream.png\" alt=\"stream\" /> <a href=\"{$streamsUrl}/{$stream}\">{$stream}</a></li>";
    }
    $streams .= "</ul>";
    $pageContent = getFileIndex($defaultMp3Dir) . $streams;
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
    -moz-box-shadow:4px 4px 0px silver;
    -webkit-box-shadow:4px 4px 0px silver;
    -o-box-shadow:4px 4px 0px silver;
    box-shadow:4px 4px 0px silver;
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
    -moz-box-shadow:4px 4px 0px silver;
    -webkit-box-shadow:4px 4px 0px silver;
    -o-box-shadow:4px 4px 0px silver;
    box-shadow:4px 4px 0px silver;
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

.dirlink, .dirlinkcover {
    cursor:pointer;
}

.dirlinkcover {
    font-size:.75em;
}

.dropimg {
    width:64px;
    height:64px;
    margin-right:4px;
}

.dirlinkcover:hover {
/*    background-color:#ececec;*/
    font-weight:bold;
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
    -moz-box-shadow:4px 4px 0px silver;
    -webkit-box-shadow:4px 4px 0px silver;
    -o-box-shadow:4px 4px 0px silver;
    box-shadow:4px 4px 0px silver;
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
    -moz-box-shadow:4px 4px 0px silver;
    -webkit-box-shadow:4px 4px 0px silver;
    -o-box-shadow:4px 4px 0px silver;
    box-shadow:4px 4px 0px silver;
}

</style>
<script type="text/javascript">
function toggleMusicOn(url) {
    if ($(".m3uplayer").size() > 0 && url == $("#theurl").data("url")) {
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();
        if ( playlist.length > 0 ) {
            if ($("#playbutton").html() == "Play") {
                player.sendEvent('PLAY', 'true');
                $("#playbutton").html("Pause");
            } else {
                player.sendEvent('PLAY', 'false');
                $("#playbutton").html("Play");
            }
        }
    } else {
        location.href = "index.php?action=createPlaylist&dir=" + encodeURIComponent(url);
    }
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    //location.href = url;
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=openDir&url=" + url + "&dir=" + url,
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

$(document).ready(function(){
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
