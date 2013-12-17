<?php

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

$pageContent .= buildCommandTable("find");

$pageContent .= <<<eof
    </tbody>
</table>
eof;

if (!$_SESSION['foundAllDeps']) {
    $pageContent .= "<div class=\"alert alert-danger\">The setup process cannot continue until all dependencies are met.</div>";
} else {
    $pageContent .= "<div class=\"alert alert-success\">All dependencies found. Time to configure the application.</div>";
    $pageContent .= "<button type=\"button\" class=\"btn btn-primary\" onclick=\"location.href='{$_SERVER['PHP_SELF']}?a=setConfigWizard'\">Next</button>";
}
