<?php
define("STREAMS", 1);

session_start();
$sessid = session_id();

ob_start("ob_gzhandler");

require_once("lib/auth.php");
require_once("lib/config.php");
require_once("lib/ws-php-library.php");
require_once("lib/stopwords.php");
require_once("lib/streams.lib.php");
require_once("ajax.php");

if ($logging) {
    file_put_contents($logfile, date("Y-m-d H:i:s") . " ::: " . $_SERVER['REMOTE_ADDR'] . " ::: " 
            . $_SERVER['HTTP_USER_AGENT'] . " ::: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
}

if (isset($_SESSION['u']) && strlen($_SESSION['u']) > 0) {
    $sessid = $_SESSION['u'];
}

if ($_GET['action'] == "downloadAlbum" && $_GET['dir'] != "") {
    if (is_dir($defaultMp3Dir . "/" . $_GET['dir'])) {
        $theDir = preg_replace("/^.+\/(.+)$/i", "\${1}", $_GET['dir']);
        $curdir = getcwd();
        chdir($defaultMp3Dir . "/" . $_GET['dir']);
        chdir("..");
        if (!file_exists("{$tmpDir}/downloadAlbum")) {
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
} else if ($_GET['action'] == "download") {
    $filename = preg_replace("/^.*\/(.*)$/i", "$1", $_GET['file']);
    header("Content-type: applications/x-download");
    header("Content-Length: " . filesize($defaultMp3Dir . '/' . $_GET['file']));
    header("Content-Disposition: attachment; filename=" . basename($filename));
    readfile($defaultMp3Dir . '/' . $_GET['file']);
    die();
}

$pageContent .= openTheDir($_GET['dir']);

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$viewport = "";
// Current the styles do not look well on phones
// Coming soon.
if (preg_match("/(Android|iPhone|Phone|iPad|Nexus)/i", $_SERVER['HTTP_USER_AGENT'])) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
}

$a_indextmpl = array("viewport" => $viewport, "pageContent" => $pageContent, "message" => $message);
$html = apply_template("tmpl/index.tmpl", $a_indextmpl);

/**
 * Actually print the xHTML
 */
ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();
