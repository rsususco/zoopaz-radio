<?php
define("STREAMS", 1);

session_start();
$sessid = session_id();

require_once("lib/auth.php");
require_once("lib/config.php");
require_once("lib/ws-php-library.php");
require_once("lib/streams.lib.php");

if ($_GET['action'] == "createPlaylistJs" && file_exists($defaultMp3Dir . '/' . $_GET['dir']) 
        && is_dir($defaultMp3Dir . '/' . $_GET['dir'])) {
    $curdir = getcwd();

    chdir($defaultMp3Dir . '/' . $_GET['dir']);

    $a_files = glob("*.{m4a,MPA,mp3,MP3,ogg,OGG}", GLOB_BRACE);
    $fileName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $_GET['dir']);
    $filename = preg_replace("/__+/", "_", $filename);

    chdir($streamsDir);

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
        $playlist .= "{'file':'{$js_directMusicUrl}', 'title':'{$js_mp3}'},";
    }

    chdir($curdir);

    $a_indextmpl = array("playlist" => $playlist);
    $flashPlayer = apply_template("tmpl/player.tmpl", $a_indextmpl);

    // This #theurl span is required. Without it the player javascript
    // doesn't function. The pause button will just restart and play the list.
    $esc_dir = preg_quote($_GET['dir']);
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

    ob_start();
    ob_implicit_flush(0);
    print($html);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "openDir") {
    $pageContent = openTheDir($pageContent);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
} else if ($_GET['action'] == "search") {
    $pageContent = search($_GET['q']);

    ob_start();
    ob_implicit_flush(0);
    print($pageContent);
    print_gzipped_page();
    die();
}
