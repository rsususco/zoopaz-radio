<?php

/*
Copyright 2014 Weldon Sams

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

session_start();
$sessid = session_id();
define("installer", "verification");

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once("lib/WsTmpl.php");
require_once("lib/form-fields-for-config.php");
require_once("lib/form-fields-for-auth.php");

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

$viewport = "";
$mobileCss = "";
if (preg_match("/(Android|iPhone|Phone|iPad|Nexus)/i", $_SERVER['HTTP_USER_AGENT'])) {
    $viewport = '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />';
    $t->setData(array());
    $t->setFile("{$cfg->streamsRootDir}/tmpl/mobile-css.tmpl");
    $mobileCss = $t->compile();
}

ob_start("ob_gzhandler");

$t = new WsTmpl();

if (isset($_GET['a']) && $_GET['a'] == "start-over") {
    unset($_SESSION['step']);
    unset($_SESSION['config-step']);
    unset($_SESSION['auth-step']);
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

if (!isset($_SESSION['step']) || $_SESSION['step'] == "" || $_SESSION['step'] == "start-wizard") {
    require_once("lib/start-config-wizard.php");
} else if ($_SESSION['step'] == "config-wizard") {
    $formFieldsForConfig = getFormFieldsForConfig();
    if (file_exists("lib/Config.php")) {
        require_once("lib/Config.php");
        $cfg = Config::getInstance();
    }
    require_once("lib/form-config-wizard.php");
} else if ($_SESSION['step'] == "auth-wizard") {
    if (file_exists("lib/Auth.php")) {
        require_once("lib/Auth.php");
        $auth = new Auth();
    }
    $formFieldsForAuth = getFormFieldsForAuth();
    require_once("lib/form-auth-wizard.php");
} else if ($_SESSION['step'] == "end-wizard") {
    require_once("lib/end-config-wizard.php");
}

$t->setData(array("viewport" => $viewport, "pageContent" => $pageContent, "mobileCss" => $mobileCss));
$t->setFile("tmpl/installer.tmpl");
$html = $t->compile();

/**
 * Return page
 */
ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();
