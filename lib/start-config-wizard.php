<?php if (!defined("installer")) { exit(); }

$_SESSION['configSetup'] = array();
$_SESSION['authSetup'] = array();

if (isset($_GET['a']) && $_GET['a'] == "setConfigWizard") {
    $_SESSION['step'] = "config-wizard";
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

function doesCommandExist($cmd) {
    ob_start();
    system("/bin/bash which {$cmd}");
    $c = ob_get_contents();
    ob_end_clean();
    if (!isset($c) || $c == null || trim($c) == "") {
        return false;
    }
    return $c;
}

function buildCommandTable($cmd) {
    if ($location = doesCommandExist($cmd)) {
        $pageContent .= "<tr><td><code>{$cmd}</code></td><td class=\"success\"><code>{$location}</code></td></tr>";
    } else {
        $_SESSION['foundAllDeps'] = false;
        $pageContent .= "<tr><td><code>{$cmd}</code></td><td class=\"danger\">Not found</td></tr>";
    }
    return $pageContent;
}

$pageContent .= <<<eof
<h1>Required Dependencies</h1>
<table class="table table-bordered">
    <thead>
        <tr><th>Command</th><th>Location</th></tr>
    </thead>
    <tbody>
eof;

$_SESSION['foundAllDeps'] = true;

$cmds = array("find", "montage", "mogrify", "zip", "rm");
foreach ($cmds as $cmd) {
    $pageContent .= buildCommandTable($cmd);
}

$exampleConfig = "lib/example.Config.php";
if (!file_exists($exampleConfig)) {
    $_SESSION['foundAllDeps'] = false;
    $pageContent .= "<tr><td class=\"danger\" colspan=\"2\">This installer requires <code>{$exampleConfig}</code>. If it has been deleted, you can simply restore from [<a target=\"_blank\" href=\"https://github.com/wsams/WsMp3Streamer/tree/master/lib\">https://github.com/wsams/WsMp3Streamer/tree/master/lib</a>].</td></tr>";
}

$exampleAuth = "lib/example.Auth.php";
if (!file_exists($exampleAuth)) {
    $_SESSION['foundAllDeps'] = false;
    $pageContent .= "<tr><td class=\"danger\" colspan=\"2\">This installer requires <code>{$exampleAuth}</code>. If it has been deleted, you can simply restore from [<a target=\"_blank\" href=\"https://github.com/wsams/WsMp3Streamer/tree/master/lib\">https://github.com/wsams/WsMp3Streamer/tree/master/lib</a>].</td></tr>";
}

$pageContent .= <<<eof
    </tbody>
</table>
eof;

if (!$_SESSION['foundAllDeps']) {
    $pageContent .= "<div class=\"alert alert-danger\">The setup process cannot continue until all dependencies are met.</div>";
} else {
    $pageContent .= "<div class=\"alert alert-success\">All dependencies found. Time to configure the application.</div>";
    $pageContent .= "<button type=\"button\" class=\"btn btn-primary\" onclick=\"location.href='{$_SERVER['PHP_SELF']}?a=setConfigWizard'\" id=\"nextButton\">Next</button>";
}
