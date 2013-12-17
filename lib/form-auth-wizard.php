<?php
/*
    * Need to check to see if we're on the last field.
    * Add an currentAction={$p['var']} nextAction={$p['var']} to the <form>.
    * First page needs to check all dependencies required by the application before allowing you to configure.
    * Need one set of steps for Auth.php and one for Auth.php.
    * Last page should tell you to remove or rename installer.php, or create a lock file that prevents it from being used twice.
*/

if (!isset($_SESSION['auth-step'])) {
    $_SESSION['auth-step'] = 1;
}

if (isset($_GET['a']) && $_GET['a'] == "setAuth") {
    if (isset($_POST['setAuthButton']) && $_POST['setAuthButton'] == "next-button") {
        $next = $_GET['next'];
    } else if (isset($_POST['setAuthButton']) && $_POST['setAuthButton'] == "previous-button") {
        $next = $_GET['prev'];
    }

    if (intval($next) > 0) {
        $_SESSION['auth-step'] = $next;
    } else if (isset($_GET['next']) && $next == "next") {
        $_SESSION['step'] = "end-wizard";
        header("Location:{$_SERVER['PHP_SELF']}");
        exit();
    }
}

$totalFields = count($formFieldsForAuth);
$currentField = intval($_SESSION['auth-step']);
$p = $formFieldsForAuth[$currentField - 1];

if (intval($currentField) == intval($totalFields)) {
    // This is the last step.
    $nextField = "next";
    $prevField = $currentField - 1;
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setAuthButton\" class=\"btn btn-primary\">Next</button>";
    $prevButton = "<button type=\"submit\" value=\"previous-button\" name=\"setAuthButton\" class=\"btn btn-primary\">Previous</button>";
    $success .= "<div class=\"alert alert-success\">Configuration just about finished, next is a summary before writing configs.</div>";
} else if (intval($currentField) == 1) {
    // This is the first step.
    $nextField = $currentField + 1;
    $prevField = "";
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setAuthButton\" class=\"btn btn-primary\">Next</button>";
    $prevButton = "";
} else {
    // In-between
    $nextField = $currentField + 1;
    $prevField = $currentField - 1;
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setAuthButton\" class=\"btn btn-primary\">Next</button>";
    $prevButton = "<button type=\"submit\" value=\"previous-button\" name=\"setAuthButton\" class=\"btn btn-primary\">Previous</button>";
}
$link = "{$_SERVER['PHP_SELF']}?a=setAuth&amp;next={$nextField}&amp;prev={$prevField}";

$pageContent .= "<form role=\"form\" action=\"{$link}\" method=\"post\">";
$pageContent .= "<h1>" . $currentField . " of " . $totalFields . "</h1>";
$pageContent .= "<div class=\"panel panel-default\">";
$pageContent .= "<div class=\"panel-heading\">Set auth parameter <code>{$p['var']}</code></div>";
$pageContent .= "<div class=\"panel-body\">";
$pageContent .= "<p>{$p['desc']}</p>";
if ($p['isboolean']) {
    $pageContent .= <<<eof
<div class="radio">
    <label>
        <input type="radio" name="{$p['var']}" id="{$p['var']}" value="true" checked="checked" />
        true
    </label>
</div>
<div class="radio">
    <label>
        <input type="radio" name="{$p['var']}" id="{$p['var']}" value="false" />
        false
    </label>
</div>
eof;
} else if ($p['isusers']) {
    $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." />
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input name="password[]" type="password" class="form-control" placeholder="Password..." />
  </div>
  <p class="btn btn-success" style="margin-bottom:16px;" id="addAnotherUser">Add another</p><br /> 
eof;
} else if ($p['isrestrictedusers']) {
    $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." />
  </div>
  <p class="btn btn-success" style="margin-bottom:16px;" id="addAnotherRestrictedUser">Add another</p><br /> 
eof;
} else {
    $pageContent .= <<<eof
  <div class="form-group">
    <label for="">{$p['var']}</label>
    <input name="{$p['var']}" type="text" class="form-control" id="{$p['var']}" placeholder="{$p['exp']}" value="{$p['exp']}" />
  </div>
eof;
    }
$pageContent .= <<<eof
        </div>
    </div>
    {$prevButton} {$nextButton}
</form>
<br />
{$success}
<br />
<button type="button" class="btn btn-danger" onclick="location.href='{$_SERVER['PHP_SELF']}?a=start-over'">Start over</button>
eof;
