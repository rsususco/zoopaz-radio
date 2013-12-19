<?php
/*
    * Need to check to see if we're on the last field.
    * Add an currentAction={$p['var']} nextAction={$p['var']} to the <form>.
    * First page needs to check all dependencies required by the application before allowing you to configure.
    * Need one set of steps for Config.php and one for Auth.php.
    * Last page should tell you to remove or rename installer.php, or create a lock file that prevents it from being used twice.
*/

if (!isset($_SESSION['config-step'])) {
    $_SESSION['config-step'] = 1;
}

if (isset($_GET['a']) && $_GET['a'] == "setConfig") {
    if (isset($_POST['setConfigButton']) && $_POST['setConfigButton'] == "next-button") {
        $next = $_GET['next'];
    } else if (isset($_POST['setConfigButton']) && $_POST['setConfigButton'] == "previous-button") {
        $next = $_GET['prev'];
    }

    if (isset($_POST['fieldValue']) && isset($_GET['field'])) {
        $field = $_GET['field'];
        $fieldValue = $_POST['fieldValue'];
        $_SESSION['configSetup'][] = array($field => $fieldValue);
    }

    if (intval($next) > 0) {
        $_SESSION['config-step'] = $next;
    } else if (isset($_GET['next']) && $next == "next") {
        $_SESSION['step'] = "auth-wizard";
        header("Location:{$_SERVER['PHP_SELF']}");
        exit();
    }
}

$totalFields = count($formFieldsForConfig);
$currentField = intval($_SESSION['config-step']);
$p = $formFieldsForConfig[$currentField - 1];

if (intval($currentField) == intval($totalFields)) {
    // This is the last step.
    $nextField = "next";
    $prevField = $currentField - 1;
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setConfigButton\" class=\"btn btn-primary\">Next</button>";
    $prevButton = "<button type=\"submit\" value=\"previous-button\" name=\"setConfigButton\" class=\"btn btn-primary\">Previous</button>";
    $success .= "<div class=\"alert alert-success\">Config parameters all set, next are the Authority parameters.</div>";
} else if (intval($currentField) == 1) {
    // This is the first step.
    $nextField = $currentField + 1;
    $prevField = "";
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setConfigButton\" class=\"btn btn-primary\">Next</button>";
    $prevButton = "";
} else {
    // In-between
    $nextField = $currentField + 1;
    $prevField = $currentField - 1;
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setConfigButton\" class=\"btn btn-primary\" id=\"nextButton\">Next</button>";
    $prevButton = "<button type=\"submit\" value=\"previous-button\" name=\"setConfigButton\" class=\"btn btn-primary\" id=\"prevButton\">Previous</button>";
}
$link = "{$_SERVER['PHP_SELF']}?a=setConfig&amp;next={$nextField}&amp;prev={$prevField}&amp;field={$p['var']}";

$pageContent .= "<form role=\"form\" action=\"{$link}\" method=\"post\">";
$pageContent .= "<h1>" . $currentField . " of " . $totalFields . "</h1>";
$pageContent .= "<div class=\"panel panel-default\">";
$pageContent .= "<div class=\"panel-heading\">Set config parameter <code>{$p['var']}</code></div>";
$pageContent .= "<div class=\"panel-body\">";
$pageContent .= "<p>{$p['desc']}</p>";
if ($p['isboolean']) {
    if ($cfg != null) {
        $isset = false;
        foreach ($_SESSION['configSetup'] as $csk=>$csv) {
            if (isset($csv[$p['var']])) {
                $isset = true;
                $val = $csv[$p['var']];
            }
        }
        if (!$isset) {
            $val = $cfg->$p['var'];
        }
    } else {
        $val = $p['exp'];
    }
    if ($val === true) {
        $btChecked = "checked=\"checked\"";
        $bfChecked = "";
    } else {
        $btChecked = "";
        $bfChecked = "checked=\"checked\"";
    }
    $pageContent .= <<<eof
<div class="radio">
    <label>
        <input type="radio" name="fieldValue" id="{$p['var']}" value="true" {$btChecked} />
        true
    </label>
</div>
<div class="radio">
    <label>
        <input type="radio" name="fieldValue" id="{$p['var']}" value="false" {$bfChecked} />
        false
    </label>
</div>
eof;
} else {
    if ($cfg != null) {
        $isset = false;
        foreach ($_SESSION['configSetup'] as $csk=>$csv) {
            if (isset($csv[$p['var']])) {
                $isset = true;
                $val = $csv[$p['var']];
            }
        }
        if (!$isset) {
            if ($p['var'] == "validMusicTypes") {
                $val = implode($cfg->$p['var'], ", ");
            } else {
                $val = $cfg->$p['var'];
            }
        }
    } else {
        $val = $p['exp'];
    }
    $pageContent .= <<<eof
  <div class="form-group">
    <label for="">{$p['var']}</label>
    <input name="fieldValue" type="text" class="form-control" id="{$p['var']}" placeholder="{$p['exp']}" value="{$val}" />
  </div>
eof;
}
$pageContent .= <<<eof
        </div>
    </div>
    <br />
    {$success}
    <br />
    {$prevButton} {$nextButton}
</form>
<br />
<button type="button" class="btn btn-danger" onclick="location.href='{$_SERVER['PHP_SELF']}?a=start-over'">Start over</button>
eof;
