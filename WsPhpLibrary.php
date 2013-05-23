<?php
/**
 * Weldon Sams - PHP Library - 3.0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The following functions were not written by myself, get_rnd_iv()
 * encrypt_md5(), decrypt_md5(), rmdir_r(), validate_xml()
 * validate_xml_return_errors(), writeUTF8File(). I believe the were all
 * found on http://docs.php.net .
 *
 * Current Functions
 * -----------------
 * Command to get list from this file: cat ws-php-library-x.y.z.php | grep "^function" | perl -pe "s/^function +(.*)\(.*$/\$1()/g" | sort
 *
 * curl_file_get_contents()
 * dir_filesize()
 * encrypt_string()
 * file_put_contents() - Must uncomment to use - this is only if you are using PHP4.
 * get_rnd_iv()
 * human_filesize()
 * is_email()
 * last_modified()
 * md5_decrypt()
 * md5_encrypt()
 * msg()
 * mya() --\
 * myf()    \
 * myfa()    \
 * myfq()     > MySQL functions
 * mynr()    /
 * myq()    /
 * myv() --/
 * removeBOM()
 * rmdir_r()
 * rmn()
 * rndstr()
 * show_msg()
 * smart_stripslashes()
 * string2link()
 * sv()
 * text_preformatting()
 * validate_xml()
 * validate_xml_return_errors()
 * writeUTF8File()
 * ws_read_file()
 * ws_register_var()
 * ws_touch()
 * ws_write_file()
 * xss_safe_string()
 */

/**
 * The following section contains variables I use often. I have not defined them
 * as constants, so be aware when coding that you don't overwrite them if you plan
 * on using them.
 */

$httphost = isset($httphost) ? trigger_error('$httphost is already defined',E_USER_WARNING) : sv('HTTP_HOST');
$useragent = isset($useragent) ? trigger_error('$useragent is already defined',E_USER_WARNING) : sv('HTTP_USER_AGENT');
$phpself = isset($phpself) ? trigger_error('$phpself is already defined',E_USER_WARNING) : sv('PHP_SELF');
$qs = isset($qs) ? trigger_error('$qs is already defined',E_USER_WARNING) : sv('QUERY_STRING');
$referer = isset($referer) ? trigger_error('$referer is already defined',E_USER_WARNING) : sv('HTTP_REFERER');
$remoteip = isset($remoteip) ? trigger_error('$remoteip is already defined',E_USER_WARNING) : sv('REMOTE_ADDR');
$requesturi = isset($requesturi) ? trigger_error('$requesturi is already defined',E_USER_WARNING) : sv('REQUEST_URI');
#$whoami = isset($whoami) ? trigger_error('$whoami is already defined',E_USER_WARNING) : `whoami`; # On UNIX this will give you the username of the account you're running under.

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
	return round($size/pow(1000, ($i = floor(log($size, 1000)))), 2) . "<span class='filesize_type'>" . $filesizename[$i] . "</span>";

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
 * Cross site scripting string filter.
 * Used when displaying user input.
 */

function xss_safe_string($string)
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

		file_put_contents($file,$newfile);

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
	if (!is_dir($path))
	{
		return filesize($path);
	}

	$size=0;
	foreach (scandir($path) as $file)
	{
		if ($file=='.' or $file=='..')
		{
			continue;
		}
		$size+=dir_filesize($path.'/'.$file);
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
 *		 error indexes separated by a comma only. Example would be,
 *
 *		 show_msg("error_msg_1,error_msg_2");
 *	
 *		 This will print out the errors in an HTML unordered list using
 *		 the style from the msg() function above in this library.
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

