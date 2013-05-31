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

require_once("lib/actions.php");

$pageContent .= openTheDir($_GET['dir']);

if (isset($_SESSION['message']) && $_SESSION['message'] != "") {
    $message = "<div class='message'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
}

$viewport = "";
// Current the styles do not look well on phones
// Coming soon.
$isMobile = false;
$jsMobileVar = "isMobile = false;";
$mobileCss = "";
if (preg_match("/(Android|iPhone|Phone|iPad|Nexus)/i", $_SERVER['HTTP_USER_AGENT'])) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
    $isMobile = true;
    $jsMobileVar = "isMobile = true;";
    $mobileCss = apply_template("tmpl/mobile-css.tmpl", array());
}

$a_indextmpl = array("viewport" => $viewport, "pageContent" => $pageContent, "message" => $message, "jsMobileVar" => $jsMobileVar, "mobileCss" => $mobileCss);
$html = apply_template("tmpl/index.tmpl", $a_indextmpl);

/**
 * Actually print the xHTML
 */
ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();
