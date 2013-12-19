<?php if (!defined("installer")) { exit(); }

function replaceConfigVars(&$configFile, $var, $val) {
    foreach ($configFile as $k=>$l) {
        $l = rtrim($l, "\n");
        if (preg_match("/\\$this->{$var}/", $l)) {
            if (preg_match("/validMusicTypes/", $var)) {
                $configFile[$k] = preg_replace("/({$var})\s*=\s*(.*)$/i", "\${1} = array(\"" . preg_replace("/\s*,\s*/", "\", \"", $val) . "\");", $l);
            } else {
                $configFile[$k] = preg_replace("/({$var})\s*=\s*('|\").*?('|\")/i", "\${1} = \"{$val}\"", $l);
            }
        }
    }
}

$configFile = file("lib/example.Config.php", FILE_IGNORE_NEW_LINES);
foreach ($_SESSION['configSetup'] as $k=>$v) {
    foreach ($v as $var=>$val) {
        replaceConfigVars($configFile, $var, $val);
    }
}

$configFileContent = implode("\n", $configFile);
if (file_exists("lib/Config.php")) {
    copy("lib/Config.php", "lib/prev.Config.php");
}
file_put_contents("lib/Config.php", $configFileContent);

function replaceAuthVars(&$authFile, $var, $val) {
    foreach ($authFile as $k=>$l) {
        $l = rtrim($l, "\n");
        if (preg_match("/\\\${$var}/", $l)) {
            if ($var == "users") {
                $a = "";
                foreach ($val as $k2=>$acct) {
                    $a .= "\"{$acct['email']}\"=>\"{$acct['password']}\", ";
                }
                $a = rtrim($a, ", ");
                $authFile[$k] = preg_replace("/({$var})\s*=.*$/i", "\${1} = array({$a});", $l);
            } else if ($var == "restrictedUsers") {
                $a = "";
                foreach ($val as $k2=>$acct) {
                    $a .= "\"{$acct['email']}\", ";
                }
                $a = rtrim($a, ", ");
                $authFile[$k] = preg_replace("/({$var})\s*=.*$/i", "\${1} = array({$a});", $l);
            } else {
                $authFile[$k] = preg_replace("/({$var})\s*=\s*('|\").*?('|\")/i", "\${1} = \"{$val}\"", $l);
            }
        }
    }
}

$authFile = file("lib/example.Auth.php", FILE_IGNORE_NEW_LINES);
foreach ($_SESSION['authSetup'] as $k=>$v) {
    replaceAuthVars($authFile, $k, $v);
}

$authFileContent = implode("\n", $authFile);
if (file_exists("lib/Auth.php")) {
    copy("lib/Auth.php", "lib/prev.Auth.php");
}
file_put_contents("lib/Auth.php", $authFileContent);

if (!file_exists("js/example.streams.config.js")) {
    copy("js/example.streams.config.js", "js/streams.config.js");
}

$pageContent .= <<<eof
<br />
<div class="alert alert-success">
<p>Setup complete.</p>
<p>Before proceeding, it is advisable to rename <code>installer.php</code> or remove it entirely.</p>
<p>Next you will want to create search indexes and album art. From the command line, go 
    into <code>scripts</code> and run <code>php run.php</code> to complete the setup. 
    See <a target="_blank" href="https://github.com/wsams/WsMp3Streamer/blob/master/README.md">README</a> and <a target="_blank" href="https://github.com/wsams/WsMp3Streamer/blob/master/INSTALL.md">INSTALL</a> for more information.</p>
<p><a href="index.php">Enter site.</a></p>
</div>
<br />
<button type="button" class="btn btn-danger" onclick="location.href='{$_SERVER['PHP_SELF']}?a=start-over'">Start over</button>
eof;
