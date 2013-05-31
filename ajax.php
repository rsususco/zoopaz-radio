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
    $playlist = buildPlaylistFromDir($_GET['dir']);

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
    $pageContent = openTheDir($_GET['dir']);

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
