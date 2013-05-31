<?php if (!defined("STREAMS")) { die('CONFIG NOT DEFINED'); }

class Config {
    private static $singleton;
    public function __construct () {
        // This is the root directory that contains your web directories.
        // See the variables below for its use.
        $this->webroot = "/var/www/nas";

        $this->host = "https://www.example.com";

        // This is the root directory of your music archive.
        // Absolute path - no trailing slash.
        $this->defaultMp3Dir = "{$this->webroot}/www.example.com/htdocs/music";

        // This is the root URL to the root location of your music archive. {@see $this->defaultMp3Dir}
        // No trailing slash.
        $this->defaultMp3Url = "{$this->host}/music";

        // This is the root directory of this streaming application.
        // Absolute path - no trailing slash.
        $this->streamsRootDir = "{$this->webroot}/www.example.com/htdocs/streams";

        // This is the root URL to the root location of this streaming application. {@see $this->streamsRootDir}
        // No trailing slash.
        $this->streamsRootDirUrl = "{$this->host}/streams";

        // This is the directory where m3u files are stored in a web accessible directory of this application.
        // Absolute path - no trailing slash.
        $this->streamsDir = "{$this->webroot}/www.example.com/htdocs/streams/m3u";

        // This is the URL pointing to the m3u directory inside this application. {@see $this->streamsDir}
        // No trailing slash.
        $this->streamsUrl = "{$this->host}/streams/m3u";

        // This is a directory where temporary files are stored for downloading albums.
        // Inside $this->tmpDir a directory named 'downloadAlbum' will be created. Inside that directory
        //     we'll create zip archives for downloading.
        // Install the following cronjob if you want to clean up this directory.
        //     30 5 * * * rm -Rf /tmp/downloadAlbum/*;
        $this->tmpDir = "/tmp";

        // This array is used when logging in. Its use has been deprecated.
        $this->users = array("ENTER_USERNAME" => "ENTER_PASSWORD");

        // Turn on logging and log to $this->loglocation
        $this->logging = true;
        $this->logfile = "access.log";
    }

    public static function getInstance () {
        if (is_null(self::$singleton)) {
            self::$singleton = new Config();
        }
        return self::$singleton;
    }
}

