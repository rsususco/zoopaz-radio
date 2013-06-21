<?php
require_once("lib/Config.php");
require_once("lib/Auth.php");
require_once("../lib/StreamsSearchIndexer.php");

/**
 * There are several instances of <code>file_put_contents()</code> that
 * are commented out. This is to aid for template updates. The expected
 * output html files will need to be updated. As well as <code>copy()</code>
 */
class WsTmplTest extends PHPUnit_Framework_TestCase {

    private $cfg;
    private $auth;
    private $indexer;

    public function __construct() {
        $this->cfg = new Config();
        $this->auth = new Auth();
        $this->indexer = new StreamsSearchIndexer($this->cfg, $this->auth);
    }

    public function __destruct() {
    }

    public function testBuildDirList() {
        if (file_exists($this->dirlistFile)) {
            $this->assertTrue(unlink($this->cfg->dirlistFile));
        }
        $this->indexer->buildDirList();
        $this->assertTrue(file_exists($this->cfg->dirlistFile));
        $testFile = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/dir.list";
        //copy($this->cfg->dirlistFile, "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/dir.list");
        $this->assertEquals(file_get_contents($testFile), file_get_contents($this->cfg->dirlistFile));
    }

    public function testIsUsingFilter() {
        $got = $this->indexer->isUsingFilter();
        $expected = false;
        $this->assertEquals($expected, $got['isUsingFilter']);

        $filter = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/filter.json";
        $this->indexer->setFilter($filter);
        $o = $this->indexer->isUsingFilter();
        $got = $o['useFilter'];
        $expected = true;
        $this->assertEquals($expected, $got);
        $filterArray = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/filterArray.obj";
        $got = serialize($o['a_filter']);
        //file_put_contents($filterArray, $got);
        $expected = file_get_contents($filterArray);
        $this->assertEquals($expected, $got);

        $filter = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/filter-bad.json";
        $this->indexer->setFilter($filter);
        try {
            $o = $this->indexer->isUsingFilter();
        } catch (Exception $e) {
            $this->assertNotNull($e);
        }
    }

    public function testIndex() {
        $this->indexer->setVerbose(false);

        $filter = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/filter.json";
        $this->indexer->setFilter($filter);

        $searchdbExpected = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/search-expected.db";
        $filesdbExpected = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/files-expected.db";
        $this->indexer->setDb($searchdbExpected);
        $this->indexer->setFdb($filesdbExpected);
        // Uncomment to update the database files.
        //$this->indexer->index();

        $searchdbGot = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/search-got.db";
        if (file_exists($searchdbGot)) {
            $this->assertTrue(unlink($searchdbGot));
        }
        $filesdbGot = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/files-got.db";
        if (file_exists($filesdbGot)) {
            $this->assertTrue(unlink($filesdbGot));
        }
        $this->indexer->setDb($searchdbGot);
        $this->indexer->setFdb($filesdbGot);
        $this->indexer->index();

        $this->assertEquals(file_get_contents($searchdbExpected), file_get_contents($searchdbGot));
        $this->assertEquals(file_get_contents($filesdbExpected), file_get_contents($filesdbGot));
    }

    public function testFilter() {
        $filter = "{$this->cfg->streamsRootDir}/tests/resources/StreamsSearchIndexer/filter.json";
        $a = json_decode(file_get_contents($filter));

        $f = "Do IncludeMe please.";
        $expected = false;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);

        $f = "Do ExcludeMe please.";
        $expected = true;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);

        $f = "dO iNCLUDEmE PLEASE.";
        $expected = false;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);

        $f = "dO eXCLUDEmE PLEASE.";
        $expected = true;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);

        $f = "includeme";
        $expected = false;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);

        $f = "excludeme";
        $expected = true;
        $got = $this->indexer->filter($f, $a);
        $this->assertEquals($expected, $got);
    }

}
