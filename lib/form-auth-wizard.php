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

    if (isset($_POST['fieldValue']) && isset($_GET['field'])) {
        $field = $_GET['field'];
        $fieldValue = $_POST['fieldValue'];
        $_SESSION['authSetup'][$field] = $fieldValue;
    } else if (isset($_POST['email']) && isset($_POST['password'])) {
        // users
        foreach ($_POST['email'] as $k=>$v) {
            $_SESSION['authSetup']['users'][$k] = array("email" => $_POST['email'][$k], "password" => $_POST['password'][$k]);
        }
    } else if (isset($_POST['email']) && !isset($_POST['password'])) {
        // restricted users don't have passwords
        foreach ($_POST['email'] as $k=>$v) {
            $_SESSION['authSetup']['restrictedUsers'][$k] = array("email" => $_POST['email'][$k]);
        }
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
    $success .= "<div class=\"alert alert-success\">Click next to write configuration files and finish the setup process.</div>";
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
    $nextButton = "<button type=\"submit\" value=\"next-button\" name=\"setAuthButton\" class=\"btn btn-primary\" id=\"nextButton\">Next</button>";
    $prevButton = "<button type=\"submit\" value=\"previous-button\" name=\"setAuthButton\" class=\"btn btn-primary\" id=\"prevButton\">Previous</button>";
}
$link = "{$_SERVER['PHP_SELF']}?a=setAuth&amp;next={$nextField}&amp;prev={$prevField}&amp;field={$p['var']}";

$pageContent .= "<form role=\"form\" action=\"{$link}\" method=\"post\">";
$pageContent .= "<h1>" . $currentField . " of " . $totalFields . "</h1>";
$pageContent .= "<div class=\"panel panel-default\">";
$pageContent .= "<div class=\"panel-heading\">Set auth parameter <code>{$p['var']}</code></div>";
$pageContent .= "<div class=\"panel-body\">";
$pageContent .= "<p>{$p['desc']}</p>";
if ($p['isboolean']) {
    if ($auth != null) {
        $isset = false;
        foreach ($_SESSION['authSetup'] as $csk=>$csv) {
            if (isset($csv[$p['var']])) {
                $isset = true;
                $val = $csv[$p['var']];
            }
        }
        if (!$isset) {
            $val = $auth->$p['var'];
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
} else if ($p['isusers']) {
    if ($auth != null) {
        if ($p['var'] == "users") {
            if (isset($_SESSION['authSetup']['users']) && is_array($_SESSION['authSetup']['users'])) {
                foreach ($_SESSION['authSetup']['users'] as $k=>$v) {
                    $users[$v['email']] = $v['password'];
                }
            } else {
                $users = $auth->$p['var'];
            }

            $cnt = 0;
            foreach ($users as $email=>$password) {
                $cnt++;
                if ($cnt > 0) {
                    $remove = "<p style=\"margin-bottom:16px\" class=\"btn btn-danger\" onclick=\"removeUser(this)\">Remove</p>";
                } else {
                    $remove = "";
                }
                $pageContent .= <<<eof
<div class="new-user"><hr /><div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." value="{$email}" />
</div>
<div class="form-group">
    <label for="password">Password</label>
    <input name="password[]" type="text" class="form-control" placeholder="Password..." value="{$password}" />
    <p>You must enter a hashed password. See instructions above for using duckduckgo.com.</p>
    <p>e.g. 6322c48be847940f6d9466bf07d2ce53186ea77c</p>
</div>{$remove}</div>
eof;
            }
        } else {
            $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." />
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input name="password[]" type="text" class="form-control" placeholder="Password..." />
    <p>You must enter a hashed password. See instructions above for using duckduckgo.com.</p>
    <p>e.g. 6322c48be847940f6d9466bf07d2ce53186ea77c</p>
  </div>
  <p class="btn btn-success" style="margin-bottom:16px;" id="addAnotherUser">Add another</p><br /> 
eof;
        }
    } else {
        $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." />
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input name="password[]" type="text" class="form-control" placeholder="Password..." />
    <p>You must enter a hashed password. See instructions above for using duckduckgo.com.</p>
    <p>e.g. 6322c48be847940f6d9466bf07d2ce53186ea77c</p>
  </div>
eof;
    }
    $pageContent .= <<<eof
  <p class="btn btn-success" style="margin-bottom:16px;" id="addAnotherUser">Add another</p><br /> 
eof;
} else if ($p['isrestrictedusers']) {
    if ($auth != null) {
        if ($p['var'] == "restrictedUsers") {
            if (isset($_SESSION['authSetup']['restrictedUsers']) && is_array($_SESSION['authSetup']['restrictedUsers'])) {
                foreach ($_SESSION['authSetup']['restrictedUsers'] as $k=>$v) {
                    $restrictedUsers[] = $v['email'];
                }
            } else {
                $restrictedUsers = $auth->$p['var'];
            }

            $cnt = 0;
            foreach ($restrictedUsers as $email) {
                $cnt++;
                if ($cnt > 0) {
                    $remove = "<p style=\"margin-bottom:16px\" class=\"btn btn-danger\" onclick=\"removeUser(this)\">Remove</p>";
                } else {
                    $remove = "";
                }
                $pageContent .= <<<eof
<div class="new-user"><hr /><div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." value="{$email}" />
</div>{$remove}</div>
eof;
            }
        } else {
            $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." value="{$email}" />
  </div>
eof;
        }
    } else {
        $pageContent .= <<<eof
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email[]" type="text" class="form-control" placeholder="Email..." />
  </div>
eof;
    }
    $pageContent .= <<<eof
  <p class="btn btn-success" style="margin-bottom:16px;" id="addAnotherRestrictedUser">Add another</p><br /> 
eof;
} else {
    if ($auth != null) {
        $val = $auth->$p['var'];
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
