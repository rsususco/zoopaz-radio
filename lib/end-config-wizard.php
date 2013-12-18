<?php

ob_start();
print("<pre>"); var_dump($_SESSION); print("</pre>");
$c = ob_get_contents();
ob_end_clean();

$pageContent .= $c; 

$pageContent .= <<<eof
<button type="button" class="btn btn-danger" onclick="location.href='{$_SERVER['PHP_SELF']}?a=start-over'">Start over</button>
eof;
