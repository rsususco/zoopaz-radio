<?php

/*
Copyright 2013 Weldon Sams

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

/**
 * The following section contains variables I use often. I have not defined them
 * as constants, so be aware when coding that you don't overwrite them if you plan
 * on using them.
 */

/**
 * text_preformatting() takes a string and returns it with any spaces
 * converted to &#160; and any tabs (\t) turned into five &#160;
 * @param string $input (Enter a string)
 * @return Returns a string with tabs converted to five &#160;'s. Two spaces will be converted to &#160_ where _ is a space.
 */

function text_preformatting($input)
{
    $match = array("  ","/\t/");
    $replace = array("&#160; ","&#160;&#160;&#160;&#160;&#160;");

    return preg_replace($match,$replace,$input);
}

/**
 * The next three functions are used to encrypt strings and then decrypt them
 */

function get_rnd_iv($iv_len)
{
    $iv = '';
    while ($iv_len-- > 0) {
        $iv .= chr(mt_rand() & 0xff);
    }

    return $iv;
}

function md5_encrypt($plain_text, $password, $iv_len = 16)
{
    $plain_text .= "\x13";
    $n = strlen($plain_text);
    if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
    $i = 0;
    $enc_text = get_rnd_iv($iv_len);
    $iv = substr($password ^ $enc_text, 0, 512);
    while($i < $n)
    {
        $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
        $enc_text .= $block;
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }

    return base64_encode($enc_text);
}

function md5_decrypt($enc_text, $password, $iv_len = 16)
{
    $enc_text = base64_decode($enc_text);
    $n = strlen($enc_text);
    $i = $iv_len;
    $plain_text = '';
    $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
    while($i < $n)
    {
        $block = substr($enc_text, $i, 16);
        $plain_text .= $block ^ pack('H*', md5($iv));
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }

    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}

/**
 * Human readable filesize
 */

function human_filesize($size){
    if(is_file($size)){
        $size = filesize($size);
    }
    else{
        #> $size is already assumed to be in bytes.
    }
    #> I set $size = 1; if it was 0 to prevent dividing by zero. It may be bad
    #> practice, but seeing a size of 1byte is better than a big error message.
    #> I'll fix this if I have time. Who uploads a zero byte file anyway?
    if($size == 0){
        $size = 1;
    }
    $filesizename = array("bytes", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb");
    return round($size/pow(1000, ($i = floor(log($size, 1000)))), 2) . $filesizename[$i];

}

/**
 * Remove a directory recursively
 */

function rmdir_r($directory, $empty=FALSE){
    if(substr($directory,-1) == '/'){
        $directory = substr($directory,0,-1);
    }
    if(!file_exists($directory) || !is_dir($directory)){
        return FALSE;
    }
    elseif(is_readable($directory)){
        $handle = opendir($directory);
        while(FALSE !== ($item = readdir($handle))){
            if($item != '.' && $item != '..'){
                $path = $directory.'/'.$item;
                if(is_dir($path)){
                    rmdir_r($path);
                }
                else{
                    unlink($path);
                }
            }
        }
        closedir($handle);
        if($empty == FALSE){
            if(!rmdir($directory)){
                return FALSE;
            }
        }
    }
    return TRUE;
}

function glob_r($dir, $pattern=FALSE)
{
    if(!$pattern){
        $pattern = "*";
    }
    $dir = preg_replace("/\/*$/", "", $dir);
    $a_curfiles = glob("{$dir}/*");
    $a_files = array();
    foreach($a_curfiles as $filename)
    {
        if(is_dir($filename))
        {
            $a_files[] = $filename;
        }
        else
        {
            $a_files = array_merge($a_files, glob_r($filename, $pattern));
        }
    }
    if(is_array($a_files))
    {
        return $a_files;
    }
    else
    {
        return FALSE;
    }
}

/**
 * Encrypt String (email obfuscation)
 * Strings are converted to html entities.
 */

function encrypt_string($orgstring){
    $encstring = '';
    for($i=0;$i<strlen($orgstring);$i++){
        $encstring .= '&#' . ord($orgstring{$i}) . ';';
    }
    return $encstring;
}

/**
 * Last Modified
 * Accepts a date format and file as input and returns the modified time of the file.
 */

function last_modified($date_format,$file){
    if($date_format == '')
    {
        $date_format = 'l, dS F, Y @ h:ia';
    }

    $last_modified = filemtime($file);
    return "Last modified " . date($date_format,$last_modified) . " EST";
}

/**
 * Cross site scripting string filter. Used when displaying user input on a web page.
 */

function xss($string)
{
    return htmlspecialchars($string,ENT_QUOTES,"ISO-8859-1");
}

/**
 * Smart stripslashes. Detects magic quotes.
 */

function smart_stripslashes($input)
{
     if(get_magic_quotes_gpc())
     {
          $input = stripslashes($input);
     }

     return $input;
}

/**
 * @param string $str (Enter any string to be filtered for mysql input.
 * @param MySQL link identifier $myid (This is the output of mysql_connect() or mysql_pconnect())
 * @return Returns a string escaped in the right character set for your MySQL database.
 */


function myv($str,$myid)
{
    if(is_numeric($str))
    {
        return $str;
    }
    else
    {
        if(get_magic_quotes_gpc())
        {
            $str = mysql_real_escape_string($str,$myid);
        }

        return $str;
    }
}

/**
 * Perform a MySQL query
 */

function myq($query,$myid){
    return mysql_query($query,$myid);
}

/**
 * Perform a MySQL query and then a fetch row on the query
 */

function myfq($query,$myid){
    return mysql_fetch_row(mysql_query($query,$myid));
}

function myfa($query,$myid){
    return mysql_fetch_assoc(mysql_query($query,$myid));
}

/**
 * Returns the number of rows in a result
 */

function mynr($row_result){
    return mysql_num_rows($row_result);
}

/**
 * Perform only a mysql fetch row. I use these in while
 * loops of the form while($row = mysql_fetch_row($query)){
 * The basic setup would be.
 * $myquery = myq("select something from something where something",$myid);
 * while($query = myf($myquery)){
 *   print($query[0]);
 * }
 *
 * or
 *
 * while($query = mya($myquery)){
 *   print($query['something']);
 * }
 */

function myf($query){
    return mysql_fetch_row($query);
}

function mya($query){
    return mysql_fetch_assoc($query);
}

function ws_touch($file)
{
    $fp = fopen($file,"w");
    if(flock($fp,LOCK_EX))
    {
        fwrite($fp,"");
        flock($fp,LOCK_UN);
        fclose($fp);

        return 1;
    }
    else
    {
        ### todo: handle the case where we don't get a lock
        return 0;
    }
}

function ws_read_file($file,$var)
{
    $var = trim($var);
    $a_file = file($file);
    foreach($a_file as $line)
    {
        if(preg_match("/^{$var}:/",$line))
        {
            $pattern = array("/^{$var}:(.*)$/","/(\r\n|\r|\n)$/");
            $replacement = array("$1","");
            $var_value = preg_replace($pattern,$replacement,$line);
        }
    }
    trim($var_value);

    return $var_value;
}

function ws_write_file($file,$var,$var_value)
{
    if(file_exists($file))
    {
        $eol = "\n";
        $is_match_found = "no"; ### if $var is not found, we'll append to the end of the file.
        $var = trim($var);
        $var_value = trim($var_value);
        $a_pattern = array("/\r\n/","/\r/","/\n/");
        $a_replacement = array("%0a","%0a","%0a");
        $var = preg_replace($a_pattern,$a_replacement,$var);
        $var_value = preg_replace($a_pattern,$a_replacement,$var_value);
        $newfile = ""; ### this will be written to the file
        $a_file = file($file);
        foreach($a_file as $line)
        {
            if(preg_match("/^{$var}:/",$line))
            {
                $is_match_found = "yes";
                $line = "{$var}:{$var_value}" . $eol;
            }

            $newfile .= $line;
        }

        if($is_match_found == "no")
        {
            $newfile .= "{$var}:{$var_value}" . $eol;
        }

        file_put_contents($file,$newfile,LOCK_EX);

        return 1;
    }
    else
    {
        return 0;
    }
}

function ws_register_var($var,$desc)
{
    $file = "db/variables/registered_variables";
    if(!file_exists($file))
    {
        if(!ws_touch($file))
        {
            return 0;
        }
    }

    if(ws_read_file($file,$var) == '')
    {
        ws_write_file($file,$var,$desc);

        return 1;
    }
    else
    {
        return 0;
    }
}

/**
 * Uncomment this block if you are using php4 and need this function
define('FILE_APPEND', 1);
function file_put_contents($n, $d, $flag = false) {
    $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
    $f = @fopen($n, $mode);
    if ($f === false) {
        return 0;
    } else {
        if (is_array($d)) $d = implode($d);
        $bytes_written = fwrite($f, $d);
        fclose($f);
        return $bytes_written;
    }
}
 */

function curl_file_get_contents($url)
{
    ### start: curl ###
    $ch = curl_init();
    $timeout = 5; // set to zero for no timeout
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    ob_start();
    curl_exec($ch);
    curl_close($ch);
    $curl_answer = ob_get_contents();
    ob_end_clean();

    return $curl_answer;
    ### end: curl ###
}

function validate_xml($xml_file)
{
    $a_errors = array();

    $xml_parser = xml_parser_create();

    #####> open a file and read data
    $fp = fopen($xml_file, 'r');
    while($xml_data = fread($fp, 4096))
    {
        #####> parse the data chunk
        if(!xml_parse($xml_parser,$xml_data,feof($fp)))
        {
            $a_errors[] = "Error: " . xml_error_string(xml_get_error_code($xml_parser));
        }

    }
    fclose($fp);

    xml_parser_free($xml_parser);

    if(count($a_errors) == 0)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function validate_xml_return_errors($xml_file)
{
      $a_errors = array();

      $xml_parser = xml_parser_create();

      #####> open a file and read data
      $fp = fopen($xml_file, 'r');
      while($xml_data = fread($fp, 4096))
      {
        #####> parse the data chunk
        if(!xml_parse($xml_parser,$xml_data,feof($fp)))
        {
          $a_errors[] = "Error: " . xml_error_string(xml_get_error_code($xml_parser));
        }

      }
      fclose($fp);

      xml_parser_free($xml_parser);

      return $a_errors;
}

function removeBOM($str="")
{
    if(substr($str,0,3) == pack("CCC",0xef,0xbb,0xbf))
    {
        $str=substr($str,3);
    }

    return $str;
}

function writeUTF8File($filename,$content) {
    $dhandle=fopen($filename,"w");
    # Now UTF-8 - Add byte order mark
    fwrite($dhandle, pack("CCC",0xef,0xbb,0xbf));
    fwrite($dhandle,$content);
    fclose($dhandle);
}

function dir_filesize($path)
{
    if(!is_dir($path))
    {
        return filesize($path);
    }

    $size=0;
    foreach(scandir($path) as $file)
    {
        if($file=='.' || $file=='..')
        {
            continue;
        }
        $size += dir_filesize($path.'/'.$file);
    }

    return $size;
}

function string2link($string)
{
    $string = preg_replace("/(?:(http:\/\/)|(www\.))(\S+\b\/?)([ [:punct:]]*)(\s|$)/i","<a href=\"http://$2$3\">$1$2$3</a>$4$5", $string);

    return $string;
}

function is_email($email)
{
    if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",$email))
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

/**
 * Remove line endings from a string
 */

function rmn($string)
{
    return preg_replace("/\n/","",$string);
}

/**
 * Print this functions output and it will display the contents of a string
 * or array within a styled division.
 */

function msg($str,$msg=NULL)
{
    if(is_array($str))
    {
        ob_start();
        print_r($str);
        $ob_contents = ob_get_contents();
        ob_clean();
        $str = "<pre>" . $ob_contents . "</pre>";
    }

    if(isset($msg))
    {
        $msg = "<h3>{$msg}</h3>";
    }

    $str = "<div style=\"margin:8px; padding:8px; background-color:silver; border:1px solid #383838; color:#383838; -moz-border-radius:16px;\">{$msg}{$str}</div>";

    return $str;
}

### Step 1: Define your custom error messages
$a_msg['error_msg_1'] = "This is an error message.";
$a_msg['error_msg_2'] = "This is a 2nd error message.";

/**
 * Step 2: Pass a string to show_msg(). This string should contain the
 *       error indexes separated by a comma only. Example would be,
 *
 *       show_msg("error_msg_1,error_msg_2");
 *  
 *       This will print out the errors in an HTML unordered list using
 *       the style from the msg() function above in this library.
 */

function show_msg($msg)
{
    ### Process any page messages.
    if(isset($msg))
    {
        $msg = preg_replace("/ /","",$msg);

        $a_msg = $GLOBALS['a_msg'];

        $li_msg = "<ul>";
        foreach(explode(",",$msg) as $v)
        {
            $li_msg .= "<li>" . $a_msg[$v] . "</li>";
        }
        $li_msg .= "</ul>";

        return msg($li_msg);
    }
}

/**
 * This function will print out a random string of 10 characters by default, or
 * you can call it with rndstr(integer) and it will display X number of random
 * characters. If you want other characters included, append them to the $characters string.
 */
function rndstr($length) {
    if($length == "")
    {
        $length = 10;
    }
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = "";

    for($p=0;$p<$length;$p++)
    {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

    return $string;
}

/**
 * This function turns, for example, $_SERVER['PHP_SELF'] into sv('PHP_SELF');
 * @param string $str (Enter a $_SERVER index)
 * @return Returns the value of $_SERVER[$str]
 */
function sv($str)
{
    return $_SERVER[$str];
}

/**
 * WTemplate is a templating engine, written for extreme simplicity.
 *
 * To use, you will need to create a template file that contains variables, let's say my.tmpl. Start a variable
 * with ;: and end a variable with :; as in ;:variable:;
 *
 * <html><head><title>;:site_title:;</title></head><body>;:my_body:;</body></html>
 *
 * Okay, that's on one line, but it doesn't have to be. Now you need to create an array. A description is in
 * the param section below for the apply_template() function. Here is an example of what the array might look like.
 *
 * Save my.tmpl and create a php file. The variables from the template, will now be the index of an associative array.
 * The values, will be the contents the variables should be replaced with. You can then print the return of this function.
 *
 * @example $a_tmpldata = array("site_title"=>"Here's my title","my_body","This is the body of my page");
 * @example print(apply_template("path/to/my.tmpl",$a_tmpldata));
 *
 * You can set the $a_tmpldata array anywhere in the script, then at the very end of the script, you print the
 * return value of apply_template() to display your page.
 *
 * If you add an html comment that's completely contained on one line, it will be replaced with nothing.
 * @example <!-- This is a comment on one line only, and I won't show up!!! -->
 *
 * @todo Add a syntax for including other templates. This will remove the extreme from extreme simplicity.
 *
 * @param string $tmplloc This string should be a path pointing to a template file.
 * @param array $a_tmpldata This array has template variables as keys with the value it should be replaced by.
 * @return Returns the template with variables replaced by contents.
 */
function apply_template($tmplloc,$a_tmpldata)
{
    $a_tmpl = file($tmplloc);

    foreach($a_tmpl as $key=>$line)
    {
        foreach($a_tmpldata as $tmplvar=>$tmplval)
        {
            $a_tmpl[$key] = rtrim(preg_replace("/;:".preg_quote($tmplvar,"/").":;/i",$tmplval,$a_tmpl[$key]),"\n");
        }
                
        ### Set variables not entered to nothing so they're not shown in the HTML.
        if(preg_match("/;:.*:;/",$a_tmpl[$key]))
        {
            $a_tmpl[$key] = preg_replace("/;:.*?:;/","",$a_tmpl[$key]);
        }

        if(preg_match("/ *<!--.*?--> */",$a_tmpl[$key]))
        {
            $a_tmpl[$key] = preg_replace("/ *<!--.*?--> */","",$a_tmpl[$key]);
        }
    }

    return implode("\n",$a_tmpl);
}

/**
 * This function retrieves a POST or GET value. It defaults to POST, so if you run the function
 * with $username = get_param("username"); it will get the post value for the $username variable sent.
 * If that value is NULL, it will try GET. Vice versa with get_param($param,"GET"); It tries GET first,
 * and if the value is NULL it will try POST.
 *
 * If you want only POST or only GET, you must set the third parameter to TRUE, but you must specify the
 * second parameter if you specify the third.
 *
 * @since 3.0.6
 * @example get_param($param,[$start,[$is_alt]]);
 * @example get_param("param1") or get_param("param2","GET"); or get_param("param3","POST",FALSE)
 * @param string $param The POST or GET param to extract.
 * @param string $start Either POST or GET. Defaults to POST. This is the first method to try. If $is_alt is FALSE, then it will not try the other.
 * @param boolean $is_alt If set to TRUE which is by default, if the first request method (POST or GET) is NULL, it will try the other. If set to FALSE, it will only try the specific request method.
 * @return string Returns the value of the parameter specified.
 */
function get_param($param,$start="POST",$is_alt=TRUE)
{
    switch($start)
    {
        case "POST":
            $param_val = isset($_POST[$param]) ? $_POST[$param] : NULL;
            if($param_val == NULL && $is_alt == TRUE)
            {
                $param_val = isset($_GET[$param]) ? $_GET[$param] : NULL;
            }
            break;
        case "GET":
            $param_val = isset($_GET[$param]) ? $_GET[$param] : NULL;
            if($param_val == NULL && $is_alt == TRUE)
            {
                $param_val = isset($_POST[$param]) ? $_POST[$param] : NULL;
            }
            break;
        default:
            $param_val = FALSE;
            trigger_error("\$start must be POST or GET.",E_USER_WARNING);
            break;
    }

    return $param_val;
}

/**
 * This function checks to see if an sqlite3 table exists.
 * @param string $MRusers_sqlite_db Path to the sqlite3 database file.
 * @param string $table_name Name of the table to check for existence.
 * @return boolean Returns TRUE if the table exists and FALSE if it does not.
 */
function sqlite3_table_exists($db_path, $table_name)
{
        $db = new SQLite3($db_path);

        ### check for mrusers table
        $result = $db->query("select name from sqlite_master where type='table' and name='" . $db->escapeString($table_name) . "'");
        $a_result = $result->fetchArray(SQLITE3_ASSOC);
        if(is_array($a_result) && count($a_result) > 0)
        {
                ### the table exists
                $db->close();
                return TRUE;
        }
        else
        {
                ### the table does not exist
                $db->close();
                return FALSE;
        }
}

/**
 * Convert SimpleXMLElement object to array date("YmdHis") . microtime();
        $curdate = preg_replace("/( |\.)/", "", $curdate);
        return $curdate;
}

/**
 * This function removes blank lines from the head and tail of a file.
 */
function ftrim($file)
{
        if(file_exists($file) && is_readable($file) && is_writable($file))
        {
                $a_file = file($file);
                /**
                 * The following array_pop() and array_shift() is because there is a line at the beginning and end that should be removed.
                 */
                $top = array_shift($a_file);
                $bottom = array_pop($a_file);
                foreach($a_file as $k=>$v)
                {
                        $v = preg_replace("/(\r|\n)/", "", $v);
                        if($v == "")
                        {
                                unset($a_file[$k]);
                        }
                        else
                        {
                                break;
                        }
                }
                $ar_file = array_reverse($a_file);
                unset($a_file);
                foreach($ar_file as $k=>$v)
                {
                        $v = preg_replace("/(\r|\n)/", "", $v);
                        if($v == "")
                        {
                                unset($ar_file[$k]);
                        }
                        else
                        {
                                break;
                        }
                }
                $a_file = array_reverse($ar_file);
                file_put_contents($file, $top . implode("", $a_file) . $bottom);
                return true;
        }
        else
    {
        return false;
    }
}
