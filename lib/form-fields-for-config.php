<?php

function getFormFieldsForConfig() {
    $formFieldsForConfig = array(
        array(
            "var" => "webroot",
            "exp" => "/var/www",
            "desc" => "This is the root directory that contains your web directories. See the variables below for its use.",
            "isboolean" => false),
        array(
            "var" => "host",
            "exp" => "https://music.example.com",
            "desc" => "This is your <code>PROTOCOL://DOMAIN</code> with no paths appended.",
            "isboolean" => false),
        array(
            "var" => "defaultMp3Dir",
            "exp" => "/var/www/music.example.com/music",
            "desc" => "Absolute path to the root directory containing your music. It should be a web accessible directory.",
            "isboolean" => false),
        array(
            "var" => "defaultMp3Url",
            "exp" => "https://music.example.com/music",
            "desc" => "This is a URL that points to the root directory containing your music.",
            "isboolean" => false),
        array(
            "var" => "streamsRootDir",
            "exp" => "/var/www/music.example.com/streams",
            "desc" => "This is the root directory of this project - the directory containing index.php.",
            "isboolean" => false),
        array(
            "var" => "streamsRootDirUrl",
            "exp" => "https://music.example.com/streams",
            "desc" => "This is the root URL of this project - the directory containing index.php.",
            "isboolean" => false),
        array(
            "var" => "tmpDir",
            "exp" => "/tmp",
            "desc" => "This is a directory where temporary files are stored for downloading albums. Inside this directory a directory named <code>downloadAlbum</code> will be created. Inside that directory we'll create zip archives for downloading. Install the following cronjob if you want to clean up this directory. <code>30 5 * * * rm -Rf /tmp/downloadAlbum/*</code>",
            "isboolean" => false),
        array(
            "var" => "streamsRootDirUrl",
            "exp" => "https://music.example.com/streams",
            "desc" => "This is the root URL of this project - the directory containing index.php.",
            "isboolean" => false),
        array(
            "var" => "logging",
            "exp" => "true or false",
            "desc" => "This allows access logging. Creates a file in the root of the project named <code>access.log</code> by default. The next page will allow you to change that file name and location.",
            "isboolean" => true),
        array(
            "var" => "logfile",
            "exp" => "/var/www/music.example.com/streams/access.log",
            "desc" => "This is a log file for logging hits to the application.",
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
            "exp" => "/var/www/music.example.com/streams/search.db",
            "desc" => "This is the search index file.",
            "isboolean" => false),
        array(
            "var" => "radioDatabase",
            "exp" => "/var/www/music.example.com/streams/files.db",
            "desc" => "This is similar to the search index file, but used for radio. It is a list of files that are randomly pulled into the radio stream based on the filters you setup. There is also a person radio index file.",
            "isboolean" => false),
        array(
            "var" => "personalRadioDatabase",
            "exp" => "files.db",
            "desc" => "This should just be a file name - not paths allowed. This file stores your personal radio index in your session directory.",
            "isboolean" => false),
        array(
            "var" => "dirlistFile",
            "exp" => "/var/www/music.example.com/streams/scripts/dir.list",
            "desc" => "This is a temporary file used to generate the search index.",
            "isboolean" => false),
        array(
            "var" => "publicListenKey",
            "exp" => "ov7w0e8ZAvw",
            "desc" => "This parameter is not currently used, but will be in the future. It's a secret key used for sharing individual albums to others without requiring the person to have an account. It will be used to create a hash placed in the URL.",
            "isboolean" => false)
    );
    return $formFieldsForConfig;
}
