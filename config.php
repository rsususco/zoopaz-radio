<?php if (!defined("STREAMS")) { die('CONFIG NOT DEFINED'); }

// This is the root directory that contains your web directories.
// See the variables below for its use.
$webroot = "/var/www/nas";

$host = "https://www.example.com";

// This is the root directory of your music archive.
// Absolute path - no trailing slash.
$defaultMp3Dir = "{$webroot}/www.example.com/htdocs/music";

// This is the root URL to the root location of your music archive. {@see $defaultMp3Dir}
// No trailing slash.
$defaultMp3Url = "{$host}/music";

// This is the root directory of this streaming application.
// Absolute path - no trailing slash.
$streamsRootDir = "{$webroot}/www.example.com/htdocs/streams";

// This is the root URL to the root location of this streaming application. {@see $streamsRootDir}
// No trailing slash.
$streamsRootDirUrl = "{$host}/streams";

// This is the directory where m3u files are stored in a web accessible directory of this application.
// Absolute path - no trailing slash.
$streamsDir = "{$webroot}/www.example.com/htdocs/streams/m3u";

// This is the URL pointing to the m3u directory inside this application. {@see $streamsDir}
// No trailing slash.
$streamsUrl = "{$host}/streams/m3u";

// This is a directory where temporary files are stored for downloading albums.
// Inside $tmpDir a directory named 'downloadAlbum' will be created. Inside that directory
//     we'll create zip archives for downloading.
// Install the following cronjob if you want to clean up this directory.
//     30 5 * * * rm -Rf /tmp/downloadAlbum/*;
$tmpDir = "/tmp";

// This array is used when logging in. Its use has been deprecated.
$users = array("ENTER_USERNAME" => "ENTER_PASSWORD");
