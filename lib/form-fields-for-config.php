<?php if (!defined("installer")) { exit(); }

function getFormFieldsForConfig() {
    $formFieldsForConfig = array(
        /*
        array(
            "var" => "webroot",
            "exp" => "/var/www",
            "desc" => "This is the root directory that contains your web directories. If you cloned the project at <code>/var/www/zoopaz-radio</code>, then you would set this value to <code>/var/www</code>",
            "isboolean" => false),
        */
        array(
            "var" => "host",
            "exp" => ($_SERVER['HTTPS'] === "on" || $_SERVER['HTTP_PORT'] === 443 ? "https://" : "http://") .  $_SERVER['HTTP_HOST'],
            "desc" => "This is your <code>PROTOCOL://DOMAIN</code> with no paths appended.",
            "isboolean" => false),
        array(
            "var" => "defaultMp3Dir",
            "exp" => preg_replace("/^(.*)\/.*$/", "\${1}", getcwd()) . "/mymusic",
            "desc" => "Absolute path to the <strong>web accessible</strong> root directory containing your music.",
            "isboolean" => false),
        array(
            "var" => "defaultMp3Url",
            "exp" => ($_SERVER['HTTPS'] === "on" || $_SERVER['HTTP_PORT'] === 443 ? "https://" : "http://") .  $_SERVER['HTTP_HOST'] . "/mymusic",
            "desc" => "This is a URL that points to the root directory containing your music.",
            "isboolean" => false),
        array(
            "var" => "streamsRootDir",
            "exp" => getcwd(),
            "desc" => "This is the root directory of this project - the directory containing index.php.",
            "isboolean" => false),
        array(
            "var" => "streamsRootDirUrl",
            "exp" => ($_SERVER['HTTPS'] === "on" || $_SERVER['HTTP_PORT'] === 443 ? "https://" : "http://") .  $_SERVER['HTTP_HOST'] . "/zoopaz-radio",
            "desc" => "This is the root URL of this project - the directory containing index.php.",
            "isboolean" => false),
        array(
            "var" => "tmpDir",
            "exp" => "/tmp",
            "desc" => "This is a directory where temporary files are stored for downloading albums. Inside this directory a directory named <code>downloadAlbum</code> will be created. Inside that directory we'll create zip archives for downloading. Install the following cronjob if you want to clean up this directory. <code>30 5 * * * rm -Rf /tmp/downloadAlbum/*</code>",
            "isboolean" => false),
        array(
            "var" => "logging",
            "exp" => "true or false",
            "desc" => "This allows access logging. Creates a file in the root of the project named <code>access.log</code> by default. The next page will allow you to change that file name and location.",
            "isboolean" => true),
        array(
            "var" => "logfile",
            "exp" => preg_replace("/^(.*)\/.*$/", "\${1}", getcwd()) . "/access.log",
            "desc" => "This is a log file for logging hits to the application. This should not be web accessible but not required.",
            "isboolean" => false),
        array(
            "var" => "validMusicTypes",
            "exp" => "mp3, m4a, ogg",
            "desc" => "A comma separated list of accepted file extensions.",
            "isboolean" => false),
        array(
            "var" => "disableStopwords",
            "exp" => "true or false",
            "desc" => "Disables stopwords when generating search index.",
            "isboolean" => true),
        array(
            "var" => "maxSearchResults",
            "exp" => "100",
            "desc" => "The maximum number of search results.",
            "isboolean" => false),
        array(
            "var" => "searchDatabase",
            "exp" => preg_replace("/^(.*)\/.*$/", "\${1}", getcwd()) . "/search.db",
            "desc" => "This is the search index file. Should not be in a web accessible directory but not required.",
            "isboolean" => false),
        array(
            "var" => "radioDatabase",
            "exp" => preg_replace("/^(.*)\/.*$/", "\${1}", getcwd()) . "/files.db",
            "desc" => "This is similar to the search index file, but used for radio. It is a list of files that are randomly pulled into the radio stream based on the filters you setup. There is also a person radio index file. Should not be in a web accessible directory but not required.",
            "isboolean" => false),
        array(
            "var" => "personalRadioDatabase",
            "exp" => "files.db",
            "desc" => "This should just be a file name - not paths allowed. This file stores your personal radio index in your session directory.",
            "isboolean" => false),
        array(
            "var" => "dirlistFile",
            "exp" => "/tmp/dir.list",
            "desc" => "This is a temporary file used to generate the search index.",
            "isboolean" => false),
        array(
            "var" => "publicListenKey",
            "exp" => "ov7w0e8ZAvw",
            "desc" => "This parameter is not currently used, but will be in the future. It's a secret key used for sharing individual albums to others without requiring the person to have an account. It will be used to create a hash placed in the URL.",
            "isboolean" => false),
        array(
            "var" => "alternateSessionDir",
            "exp" => preg_replace("/^(.*)\/.*$/", "\${1}", getcwd()) . "/stream-sessions",
            "desc" => "This parameter allows you to override the default session storage directory. Which ever directory you choose, make sure to create it.",
            "isboolean" => false),
        array(
            "var" => "hashFunction",
            "exp" => "sha1",
            "desc" => "This parameter defines a function that is responsible for hashing passwords for authentication. e.g. sha1, md5, ...",
            "isboolean" => false)
    );
    return $formFieldsForConfig;
}
