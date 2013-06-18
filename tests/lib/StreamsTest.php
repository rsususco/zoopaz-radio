<?php
require_once("lib/Config.php");
require_once("lib/Auth.php");
require_once("../lib/getid3/getid3/getid3.php");
require_once("../lib/WsTmpl.php");
require_once("../lib/Streams.php");

/**
 * There are several instances of <code>file_put_contents()</code> that
 * are commented out. This is to aid for template updates. The expected
 * output html files will need to be updated.
 */
class WsTmplTest extends PHPUnit_Framework_TestCase {

    private $cfg;
    private $auth;
    private $t;
    private $streams;
    private $htmlDir;

    public function __construct() {
        $this->cfg = new Config();
        $this->auth = new Auth();
        $this->t = new WsTmpl();
        $this->streams = new Streams($this->cfg, $this->auth, $this->t);
        $this->htmlDir = getcwd() . "/resources/Streams/html";
    }

    public function __destruct() {
        $this->rmdir_r(getcwd() . "/sessions", getcwd());
    }

    private function rmdir_r($directory, $sandbox, $empty=FALSE){
        if (!preg_match("/^" . preg_quote($sandbox, "/") . "/i", $directory)) {
            return false;
        }
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        if (!file_exists($directory) || !is_dir($directory)) {
            return FALSE;
        } else if (is_readable($directory)) {
            $handle = opendir($directory);
            while (FALSE !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $directory.'/'.$item;
                    if (is_dir($path)) {
                        $this->rmdir_r($path, $sandbox);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if ($empty == FALSE) {
                if (!rmdir($directory)) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    public function testHumanFilesize() {
        $in = 1;
        $expected = "1bytes";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12;
        $expected = "12bytes";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123;
        $expected = "123bytes";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234;
        $expected = "1.23kb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345;
        $expected = "12.35kb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456;
        $expected = "123.46kb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567;
        $expected = "1.23mb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678;
        $expected = "12.35mb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789;
        $expected = "123.46mb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898;
        $expected = "1.23gb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987;
        $expected = "12.35gb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876;
        $expected = "123.46gb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765;
        $expected = "1.23tb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654;
        $expected = "12.35tb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543;
        $expected = "123.46tb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765432;
        $expected = "1.23pb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654321;
        $expected = "12.35pb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543212;
        $expected = "123.46pb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765432123;
        $expected = "1.23eb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654321234;
        $expected = "12.35eb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543212345;
        $expected = "123.46eb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765432123456;
        $expected = "1.23zb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654321234567;
        $expected = "12.35zb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543212345678;
        $expected = "123.46zb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765432123456789;
        $expected = "1.23yb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654321234567898;
        $expected = "12.35yb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543212345678987;
        $expected = "123.46yb";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 1234567898765432123456789876;
        $expected = "1.23";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 12345678987654321234567898765;
        $expected = "12.35";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);

        $in = 123456789876543212345678987654;
        $expected = "123.46";
        $got = $this->streams->human_filesize($in);
        $this->assertEquals($expected, $got);
    }

    public function testOpenTheDir() {
        $expected = file_get_contents("{$this->htmlDir}/open-the-dir-root.html");
        $got = $this->streams->openTheDir("/");
        //file_put_contents("{$this->htmlDir}/open-the-dir-root.html", $got);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/open-the-dir-mymusic.html");
        $got = $this->streams->openTheDir("/MyMusic");
        //file_put_contents("{$this->htmlDir}/open-the-dir-mymusic.html", $got);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/open-the-dir-mymusic-rock.html");
        $got = $this->streams->openTheDir("/MyMusic/Rock");
        //file_put_contents("{$this->htmlDir}/open-the-dir-mymusic-rock.html", $got);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/open-the-dir-mymusic-rock-foobar.html");
        $got = $this->streams->openTheDir("/MyMusic/Rock/Foobar");
        //file_put_contents("{$this->htmlDir}/open-the-dir-mymusic-rock-foobar.html", $got);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/open-the-dir-mymusic-rock-foobar-bestalbum.html");
        $got = $this->streams->openTheDir("/MyMusic/Rock/Foobar/BestAlbum");
        //file_put_contents("{$this->htmlDir}/open-the-dir-mymusic-rock-foobar-bestalbum.html", $got);
        $this->assertEquals($expected, $got);

        $expected = "";
        $got = $this->streams->openTheDir("/MyMusic/Does Not Exist");
        $this->assertEquals($expected, $got);
    }

    public function testContainsMusic() {
        $expected = false;
        $got = $this->streams->containsMusic("/");
        $this->assertEquals($expected, $got);

        $expected = false;
        $got = $this->streams->containsMusic("/MyMusic");
        $this->assertEquals($expected, $got);

        $expected = false;
        $got = $this->streams->containsMusic("/MyMusic/Rock");
        $this->assertEquals($expected, $got);

        $expected = true;
        $got = $this->streams->containsMusic("/MyMusic/Rock/Foobar/BestAlbum");
        $this->assertEquals($expected, $got);

        $expected = true;
        $got = $this->streams->containsMusic("/MyMusic/Rock/Foobar/Sure");
        $this->assertEquals($expected, $got);

        $expected = true;
        $got = $this->streams->containsMusic("/MyMusic/Rock/Foobar/AnotherOne");
        $this->assertEquals($expected, $got);

        $expected = true;
        $got = $this->streams->containsMusic("/MyMusic/Jazz/SallyTheBand/Dezos");
        $this->assertEquals($expected, $got);
    }

    public function testBuildIndex() {
        $dirLink = "/MyMusic/Jazz/SallyTheBand/Dezos/";
        $dir = $this->streams->singleSlashes("{$this->cfg->defaultMp3Dir}/{$dirLink}");
        $a_files = glob("{$dir}/*");
        $expected = file_get_contents("{$this->htmlDir}/build-index-sally-dezos.html");
        $got = $this->streams->buildIndex($a_files, $dirLink, $search=false);
        $this->assertEquals($expected, $got['index']);
        $expected = true;
        $this->assertEquals($expected, $got['isMp3']);
    }

    public function testId3() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";
        $got = $this->streams->id3($dir, $file);
        $playlistTitle = "T35t Art15t &rsaquo; T35t Album &rsaquo; T35t T1tl3";
        $this->assertEquals($playlistTitle, $got['playlistTitle']);
        $artist = "T35t Art15t";
        $this->assertEquals($artist, $got['artist']);
        $album = "T35t Album";
        $this->assertEquals($album, $got['album']);
        $title = "T35t T1tl3";
        $this->assertEquals($title, $got['title']);
        $albumart = file_get_contents("{$this->htmlDir}/albumart.base64");
        $this->assertEquals($albumart, $got['albumart']);
    }

    public function testGetAlbumArt() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";
        $id3 = $this->streams->id3($dir, $file);

        // TODO: Strip those extra slashes.
        $expected = "https://www.example.com/resources/music//MyMusic/TestBand/TestAlbum/cover.jpg";
        $got = $this->streams->getAlbumArt($id3, $dir);
        $this->assertEquals($expected, $got);

        $file = "test-noid3.mp3";
        $id3 = $this->streams->id3($dir, $file);

        // TODO: Strip those extra slashes.
        $expected = "https://www.example.com/resources/music//MyMusic/TestBand/TestAlbum/cover.jpg";
        $got = $this->streams->getAlbumArt($id3, $dir);
        $this->assertEquals($expected, $got);
    }

    public function testGetId3Artist() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = "T35t Art15t";
        $got = $this->streams->getId3Artist($id3);
        $this->assertEquals($expected, $got);

        $file = "test-noid3.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = false;
        $got = $this->streams->getId3Artist($id3);
        $this->assertEquals($expected, $got);
    }

    public function testGetId3Album() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = "T35t Album";
        $got = $this->streams->getId3Album($id3);
        $this->assertEquals($expected, $got);

        $file = "test-noid3.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = false;
        $got = $this->streams->getId3Album($id3);
        $this->assertEquals($expected, $got);
    }

    public function testGetId3Title() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = "T35t T1tl3";
        $got = $this->streams->getId3Title($id3);
        $this->assertEquals($expected, $got);

        $file = "test-noid3.mp3";

        $getID3 = new getID3();
        $pageEncoding = 'UTF-8';
        $getID3->setOption(array("encoding" => $pageEncoding));
        $id3 = $getID3->analyze("{$this->cfg->defaultMp3Dir}{$dir}/{$file}");

        $expected = false;
        $got = $this->streams->getId3Title($id3);
        $this->assertEquals($expected, $got);
    }

    public function testGetFileIndex() {
        $dir = "/";
        $expected = file_get_contents("{$this->htmlDir}/get-file-index-root.html");
        $got = $this->streams->getFileIndex($dir);
        //file_put_contents("{$this->htmlDir}/get-file-index-root.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $expected = file_get_contents("{$this->htmlDir}/get-file-index-mymusic.html");
        $got = $this->streams->getFileIndex($dir);
        //file_put_contents("{$this->htmlDir}/get-file-index-mymusic.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock";
        $expected = file_get_contents("{$this->htmlDir}/get-file-index-mymusic-rock.html");
        $got = $this->streams->getFileIndex($dir);
        //file_put_contents("{$this->htmlDir}/get-file-index-mymusic-rock.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar";
        $expected = file_get_contents("{$this->htmlDir}/get-file-index-mymusic-rock-foobar.html");
        $got = $this->streams->getFileIndex($dir);
        //file_put_contents("{$this->htmlDir}/get-file-index-mymusic-rock-foobar.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar/BestAlbum";
        $expected = file_get_contents("{$this->htmlDir}/get-file-index-mymusic-rock-foobar-bestalbum.html");
        $got = $this->streams->getFileIndex($dir);
        //file_put_contents("{$this->htmlDir}/get-file-index-mymusic-rock-foobar-bestalbum.html", $got);
        $this->assertEquals($expected, $got);
    }

    function testBuildPlayerControls() {
        $dir = "/";
        $expected = file_get_contents("{$this->htmlDir}/build-player-controls-root.html");
        $got = $this->streams->buildPlayerControls($dir);
        //file_put_contents("{$this->htmlDir}/build-player-controls-root.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $expected = file_get_contents("{$this->htmlDir}/build-player-controls-mymusic.html");
        $got = $this->streams->buildPlayerControls($dir);
        //file_put_contents("{$this->htmlDir}/build-player-controls-mymusic.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock";
        $expected = file_get_contents("{$this->htmlDir}/build-player-controls-mymusic-rock.html");
        $got = $this->streams->buildPlayerControls($dir);
        //file_put_contents("{$this->htmlDir}/build-player-controls-mymusic-rock.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar";
        $expected = file_get_contents("{$this->htmlDir}/build-player-controls-mymusic-rock-foobar.html");
        $got = $this->streams->buildPlayerControls($dir);
        //file_put_contents("{$this->htmlDir}/build-player-controls-mymusic-rock-foobar.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar/BestAlbum";
        $expected = file_get_contents("{$this->htmlDir}/build-player-controls-mymusic-rock-foobar-bestalbum.html");
        $got = $this->streams->buildPlayerControls($dir);
        //file_put_contents("{$this->htmlDir}/build-player-controls-mymusic-rock-foobar-bestalbum.html", $got);
        $this->assertEquals($expected, $got);
    }

    public function testBuildSearchBox() {
        $expected = file_get_contents("{$this->htmlDir}/build-search-box.html");
        $got = $this->streams->buildSearchBox();
        //file_put_contents("{$this->htmlDir}/build-search-box.html", $got);
        $this->assertEquals($expected, $got);
    }

    public function testGetDropDownAlbums() {
        $dir = "/";
        $expected = file_get_contents("{$this->htmlDir}/drop-down-albums-root.html");
        $got = $this->streams->getDropDownAlbums($dir);
        //file_put_contents("{$this->htmlDir}/drop-down-albums-root.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $expected = file_get_contents("{$this->htmlDir}/drop-down-albums-mymusic.html");
        $got = $this->streams->getDropDownAlbums($dir);
        //file_put_contents("{$this->htmlDir}/drop-down-albums-mymusic.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock";
        $expected = file_get_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock.html");
        $got = $this->streams->getDropDownAlbums($dir);
        //file_put_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar";
        $expected = file_get_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock-foobar.html");
        $got = $this->streams->getDropDownAlbums($dir);
        //file_put_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock-foobar.html", $got);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Rock/Foobar/BestAlbum";
        $expected = file_get_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock-foobar-bestalbum.html");
        $got = $this->streams->getDropDownAlbums($dir);
        //file_put_contents("{$this->htmlDir}/drop-down-albums-mymusic-rock-foobar-bestalbum.html", $got);
        $this->assertEquals($expected, $got);
    }

    public function testSingleSlashes() {
        $str = "////MyMusic//Rock/Foobar/////////BestAlbum";
        $expected = "/MyMusic/Rock/Foobar/BestAlbum";
        $got = $this->streams->singleSlashes($str);
        $this->assertEquals($expected, $got);
    }

    public function testBuildSearchQuery() {
        $regex = "test monkey";
        $expected = array("test monkey", "test", "monkey");
        $got = $this->streams->buildSearchQuery($regex);
        $this->assertEquals($expected, $got);

        $regex = "test monkey \"free trial\"";
        $expected = array($regex, "free trial", "test", "monkey");
        $got = $this->streams->buildSearchQuery($regex);
        $this->assertEquals($expected, $got);

        $regex = "\"mysql oracle mongodb\" test monkey \"free trial\"";
        $expected = array($regex, "mysql oracle mongodb", "free trial", "test", "monkey");
        $got = $this->streams->buildSearchQuery($regex);
        $this->assertEquals($expected, $got);

        $regex = "ice cream \"mysql oracle mongodb\" test monkey \"free trial\"";
        $expected = array($regex, "mysql oracle mongodb", "free trial", "ice", "cream", "test", "monkey");
        $got = $this->streams->buildSearchQuery($regex);
        $this->assertEquals($expected, $got);

        $regex = "ice cream sandwich";
        $expected = array($regex, "ice", "cream", "sandwich");
        $got = $this->streams->buildSearchQuery($regex);
        $this->assertEquals($expected, $got);

        $regex = "+chocolate -vanilla +\"ice cream\" -sandwich +cone";
        $expected = array($regex, "+\"ice cream\"", "+chocolate", "-vanilla", "-sandwich", "+cone");
        $got = $this->streams->buildSearchQuery($regex);
        //$this->assertEquals($expected, $got);
    }

    // TODO: Let's not use search.db. This is integration oriented. Let's create a dummy array.
    public function testSearchArray() {
        $f = array("test", "the bestalbum ever", "logged in", "album", "bestalbum", "time");

        $q = "bestalbum";
        $got = $this->streams->searchArray($q, $f);
        $expected = array(1, 4);
        $this->assertEquals($expected, $got);

        $q = "test";
        $got = $this->streams->searchArray($q, $f);
        $expected = array(0);
        $this->assertEquals($expected, $got);

        $q = "aLBum TIME";
        $got = $this->streams->searchArray($q, $f);
        $expected = array(1, 3, 4, 5);
        $this->assertEquals($expected, $got);

        $q = "album test";
        $got = $this->streams->searchArray($q, $f);
        $expected = array(1, 3, 4, 0);
        $this->assertEquals($expected, $got);

        $q = "\"the bestalbum ever\"";
        $got = $this->streams->searchArray($q, $f);
        $expected = array(1);
        $this->assertEquals($expected, $got);

        $q = "the bestalbum ever";
        $got = $this->streams->searchArray($q, $f);
        // Keys are preserved when using array_unique() in searchArray().
        $expected = array(0=>1, 3=>4);
        $this->assertEquals($expected, $got);

        $testArray = array("sparkling water", "pumpkin", "beer garden", "rat race", 
                "agile", "shoots and ladders");
        $q = "\"sparkling water\"";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(0);
        $this->assertEquals($expected, $got);

        $q = "\"sparkling water\" pumpkin \"rat race\"";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(0, 3, 1);
        $this->assertEquals($expected, $got);

        $q = "\"shoots and ladders\" \"sparkling water\" pumpkin \"rat race\"";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(5, 0, 3, 1);
        $this->assertEquals($expected, $got);

        $q = "pumpkin";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(1);
        $this->assertEquals($expected, $got);

        $q = "pumpkin agile";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(1, 4);
        $this->assertEquals($expected, $got);

        $q = "\"shoots and ladders\"";
        $got = $this->streams->searchArray($q, $testArray);
        $expected = array(5);
        $this->assertEquals($expected, $got);

        $testArray2 = array("ice cream sandwich", "ice", "vanilla", "cream", "chocolate");
        $q = "ice cream sandwich";
        $got = $this->streams->searchArray($q, $testArray2);
        $expected = array(0=>0, 2=>1, 4=>3);
        $this->assertEquals($expected, $got);
    }

    function testSearch() {
        $q = "songs";
        $expected = file_get_contents("{$this->htmlDir}/search-songs.html");
        $got = $this->streams->search($q);
        //file_put_contents("{$this->htmlDir}/search-songs.html", $got);
        $this->assertEquals($expected, $got);

        $q = "bestAlbum";
        $expected = file_get_contents("{$this->htmlDir}/search-bestalbum.html");
        $got = $this->streams->search($q);
        //file_put_contents("{$this->htmlDir}/search-bestalbum.html", $got);
        $this->assertEquals($expected, $got);

        $q = "songs bestAlbum";
        $expected = file_get_contents("{$this->htmlDir}/search-songs-bestalbum.html");
        $got = $this->streams->search($q);
        //file_put_contents("{$this->htmlDir}/search-songs-bestalbum.html", $got);
        $this->assertEquals($expected, $got);

        $q = "\"dezos happysongs\"";
        $got = $this->streams->search($q);
        //file_put_contents("{$this->htmlDir}/search-quoted-dezos-happysongs.html", $got);
        $expected = file_get_contents("{$this->htmlDir}/search-quoted-dezos-happysongs.html");
        $this->assertEquals($expected, $got);
    }

    public function testBuildArrayFromDir() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $expected = array(0=>"1 - Song's.mp3", 3=>"2 - Song's.mp3", 4=>"3 - Song's.mp3", 
                5=>"4 - Song's.mp3", 6=>"5 - Song's.mp3", 7=>"6 - Song's.mp3", 8=>"7 - Song's.mp3", 
                9=>"8 - Song's.mp3", 10=>"9 - Song's.mp3", 1=>"10 - Song's.mp3", 2=>"11 - Song's.mp3");
        $got = $this->streams->buildArrayFromDir($dir);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Jazz/SallyTheBand";
        $expected = array();
        $got = $this->streams->buildArrayFromDir($dir);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Jazz";
        $expected = array();
        $got = $this->streams->buildArrayFromDir($dir);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $expected = array();
        $got = $this->streams->buildArrayFromDir($dir);
        $this->assertEquals($expected, $got);

        $dir = "/";
        $expected = array();
        $got = $this->streams->buildArrayFromDir($dir);
        $this->assertEquals($expected, $got);
    }

    public function testBuildPlaylistArrayFromDir() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $obj = "{$this->htmlDir}/playlist-array-music-jazz-sally-dezos.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromDir($dir);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Jazz/SallyTheBand";
        $obj = "{$this->htmlDir}/playlist-array-music-jazz-sally.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromDir($dir);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic/Jazz";
        $obj = "{$this->htmlDir}/playlist-array-music-jazz.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromDir($dir);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $obj = "{$this->htmlDir}/playlist-array-music.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromDir($dir);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);

        $dir = "/";
        $obj = "{$this->htmlDir}/playlist-array-root.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromDir($dir);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);
    }

    public function testBuildPlaylistArrayFromFile() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $file = "9 - Song's.mp3";
        $obj = "{$this->htmlDir}/playlist-array-dezos-9song.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistArrayFromFile($dir, $file);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);
    }

    public function testBuildPlaylistFromDir() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $json = "{$this->htmlDir}/playlist-fromdir-array-dezos-9song.json";
        $expected = file_get_contents($json);
        $got = $this->streams->buildPlaylistFromDir($dir, $file);
        //file_put_contents($json, $got);
        $this->assertEquals($expected, $got);
        return $got;
    }

    public function testBuildPlaylistFromFile() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $file = "9 - Song's.mp3";
        $json = "{$this->htmlDir}/playlist-fromfile-array-dezos-9song.json";
        $expected = file_get_contents($json);
        $got = $this->streams->buildPlaylistFromFile($dir, $file);
        //file_put_contents($json, $got);
        $this->assertEquals($expected, $got);
    }

    public function testBuildPlayerAlbumTitle() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $expected = "SallyTheBand &rsaquo; Dezos";
        $got = $this->streams->buildPlayerAlbumTitle($dir);
        $this->assertEquals($expected, $got);

        $dir = "/MyMusic";
        $expected = "MyMusic";
        $got = $this->streams->buildPlayerAlbumTitle($dir);
        $this->assertEquals($expected, $got);

        $dir = "/";
        $expected = "";
        $got = $this->streams->buildPlayerAlbumTitle($dir);
        $this->assertEquals($expected, $got);
    }

    /**
     * @depends testBuildPlaylistFromDir
     */
    public function testBuildPlayerHtml($playlist) {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $html = "{$this->htmlDir}/build-player-html-dezos-false.html";
        $expected = file_get_contents($html);
        $got = $this->streams->buildPlayerHtml($playlist, $dir, "false");
        //file_put_contents($html, $got);
        $this->assertEquals($expected, $got);
    }

    /**
     * @depends testBuildPlaylistFromDir
     */
    public function testAddToPlaylist($playlist) {
        $dir = "/MyMusic/Rock/Foobar/Sure";

        // TODO: These names are hard-coded in Streams. Maybe we shouldn't do that.
        $this->auth->currentPlaylist = "{$this->htmlDir}/currentPlaylist.obj";
        file_put_contents($this->auth->currentPlaylist, $playlist);

        $this->auth->currentPlaylistDir = "{$this->htmlDir}/currentPlaylistDir.obj";
        $playlistDir = "Foobar &rsaquo; Sure";
        file_put_contents($this->auth->currentPlaylistDir, $playlistDir);

        $got = $this->streams->addToPlaylist($dir);
        //file_put_contents("{$this->htmlDir}/add-to-playlist.json", $got);
        //file_put_contents("{$this->htmlDir}/current-playlist.json", file_get_contents($this->auth->currentPlaylist));

        $expected = "/Custom playlist";
        $got = file_get_contents($this->auth->currentPlaylistDir);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/current-playlist.json");
        $got = file_get_contents($this->auth->currentPlaylist);
        $this->assertEquals($expected, $got);
    }

    /**
     * @depends testAddToPlaylist
     */
    public function testAddToPlaylistFile() {
        $dir = "/MyMusic/Jazz/SallyTheBand/HappySongs";
        $file = "4 - Song's.mp3";

        $this->auth->currentPlaylist = "{$this->htmlDir}/currentPlaylist.obj";
        $this->auth->currentPlaylistDir = "{$this->htmlDir}/currentPlaylistDir.obj";

        $got = $this->streams->addToPlaylistFile($dir, $file);
        //file_put_contents("{$this->htmlDir}/add-to-playlist-added-file.json", $got);
        //file_put_contents("{$this->htmlDir}/current-playlist-added-file.json", file_get_contents($this->auth->currentPlaylist));

        $expected = "/Custom playlist";
        $got = file_get_contents($this->auth->currentPlaylistDir);
        $this->assertEquals($expected, $got);

        $expected = file_get_contents("{$this->htmlDir}/current-playlist-added-file.json");
        $got = file_get_contents($this->auth->currentPlaylist);
        $this->assertEquals($expected, $got);
    }

    /**
     * @depends testAddToPlaylist
     */
    public function testClearPlaylist() {
        $this->auth->currentPlaylist = "{$this->htmlDir}/currentPlaylist.obj";
        $this->auth->currentPlaylistDir = "{$this->htmlDir}/currentPlaylistDir.obj";

        $this->streams->clearPlaylist();
        $expected = array();
        $got = json_decode(file_get_contents($this->auth->currentPlaylist));
        $this->assertEquals($expected, $got);

        $expected = "/Custom playlist";
        $got = file_get_contents($this->auth->currentPlaylistDir);
        $this->assertEquals($expected, $got);
    }

    public function testGetRandomPlaylistJson() {
        $cnt = 1;
        $got = json_decode($this->streams->getRandomPlaylistJson($cnt));
        $this->assertEquals($cnt, count($got));
        $expected = "<img class=\"playlist-albumart\" data-done=\"false\" data-dir=\"\" data-file=\"\" style=\"width:0px; height:0px;\" src=\"images/clear-pix-1x1.png\" /> <span class='playlistTitle'></span>";
        foreach ($got as $k=>$v) {
            $this->assertTrue(array_key_exists("mp3", $v));
            $this->assertTrue(array_key_exists("title", $v));
            $this->assertTrue($this->verifyUrl($v->mp3));
            $this->assertEquals($this->verifyTitle($v->title), $expected);
        }

        $cnt = 5;
        $got = json_decode($this->streams->getRandomPlaylistJson($cnt));
        $this->assertEquals($cnt, count($got));
        $expected = "<img class=\"playlist-albumart\" data-done=\"false\" data-dir=\"\" data-file=\"\" style=\"width:0px; height:0px;\" src=\"images/clear-pix-1x1.png\" /> <span class='playlistTitle'></span>";
        foreach ($got as $k=>$v) {
            $this->assertTrue(array_key_exists("mp3", $v));
            $this->assertTrue(array_key_exists("title", $v));
            $this->assertTrue($this->verifyUrl($v->mp3));
            $this->assertEquals($this->verifyTitle($v->title), $expected);
        }
    }

    private function verifyUrl($url) {
        if (!preg_match("/^http/i", $url)) {
            print("FAIL: Did not find http at the beginning of $url\n");
            return false;
        }
        if (!preg_match("/\.(" . $this->cfg->getValidMusicTypes("preg") . ")$/i", $url)) {
            print("FAIL: $url did not point to a valid music type.\n");
            return false;
        }
        return true;
    }

    private function verifyTitle($title) {
        $title = preg_replace("/data-dir\s*=\s*\".*?\"/", "data-dir=\"\"", $title);
        $title = preg_replace("/data-file\s*=\s*\".*?\"/", "data-file=\"\"", $title);
        $title = preg_replace("/<span class='playlistTitle'>.*?<\/span>/", "<span class='playlistTitle'></span>", $title);
        return $title;
    }

    public function testBuildPlaylistItemArray() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";
        $obj = "{$this->htmlDir}/playlist-item-array-test.obj";
        $expected = unserialize(file_get_contents($obj));
        $got = $this->streams->buildPlaylistItemArray($dir, $file);
        //file_put_contents($obj, serialize($got));
        $this->assertEquals($expected, $got);
    }

    public function testUrlEncodeDir() {
        $dir = "/MyMusic/Test Band/Foobar's Test Album #1";
        $expected = "/MyMusic/Test%20Band/Foobar%27s%20Test%20Album%20%231";
        $got = $this->streams->urlEncodeDir($dir);
        $this->assertEquals($expected, $got);
    }

    public function testBuildPlaylistTitle() {
        $dir = "/MyMusic/TestBand/TestAlbum";
        $file = "test.mp3";
        $id3 = $this->streams->id3($dir, $file);
        $html = "{$this->htmlDir}/playlist-title.html";
        $expected = file_get_contents($html);
        $got = $this->streams->buildPlaylistTitle($id3, $dir, $file);
        //file_put_contents($html, $got);
        $this->assertEquals($expected, $got);
    }

    public function testPlayRadio() {
        $num = 3;
        $html = "{$this->htmlDir}/play-radio.html";
        $got = $this->streams->playRadio($num);
        $got = $this->stripPlaylist($got);
        //file_put_contents($html, $got);
        $expected = file_get_contents($html);
        $this->assertEquals($expected, $got);
    }

    private function stripPlaylist($html) {
        $html = preg_replace("/\[{\"mp3\".*?\"}\],/", "[],", $html);
        return $html;
    }

    public function testCreatePlaylistJs() {
        $dir = "/MyMusic/Jazz/SallyTheBand/Dezos";
        $this->auth->currentPlaylist = "{$this->htmlDir}/currentPlaylist.obj";
        $this->auth->currentPlaylistDir = "{$this->htmlDir}/currentPlaylistDir.obj";
        $html = "{$this->htmlDir}/create-playlist-js.html";
        $got = $this->streams->createPlaylistJs($dir);
        //file_put_contents($html, $got);
        $expected = file_get_contents($html);
        $this->assertEquals($expected, $got);
    }

    public function testGetHomeIndex() {
        $got = $this->streams->getHomeIndex();
        $html = "{$this->htmlDir}/get-home-index.html";
        //file_put_contents($html, $got);
        $expected = file_get_contents($html);
        $this->assertEquals($expected, $got);
    }

}
