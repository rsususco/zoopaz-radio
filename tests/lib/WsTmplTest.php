<?php
require_once("../lib/WsTmpl.php");

class WsTmplTest extends PHPUnit_Framework_TestCase {

    public function testFileProperty() {
        $file = "tmpl/index.tmpl";

        $t = new WsTmpl();
        $t->setFile($file);

        $this->assertEquals($file, $t->getFile());
    }

    public function testDataProperty() {
        $data = array("data" => "something");

        $t = new WsTmpl();
        $t->setData($data);

        $this->assertEquals($data, $t->getData());
    }

    public function testStripCommentsProperty() {
        $stripComments = false;

        $t = new WsTmpl();
        $t->setStripComments($stripComments);

        $this->assertEquals($stripComments, $t->getStripComments());
    }

    public function testAddData() {
        $data = array("title" => "The Title", "content" => "The page content.");
        $newdata = array("new1" => "More data", "new2" => "A bit more");
        $expected = array_merge($data, $newdata);
        $t = new WsTmpl();
        $t->setData($data);
        $this->assertEquals($data, $t->getData());
        $t->addData($newdata);
        $this->assertEquals($expected, $t->getData());
    }

    public function testCompile() {
        $file = "resources/tmpl/index.tmpl";
        $data = array("title" => "The Title", "content" => "The page content.");
        $t = new WsTmpl();
        $t->setFile($file);
        $t->setData($data);
        $expected = file_get_contents("resources/tmpl/index.html");
        $got = $t->compile();
        $this->assertEquals($expected, $got);
        $this->assertEquals($expected, $t->getHtml());
    }

    public function testConstructor() {
        $file = "resources/tmpl/index.tmpl";
        $data = array("title" => "The Title", "content" => "The page content.");
        $t = new WsTmpl($file, $data);
        $expected = file_get_contents("resources/tmpl/index.html");
        $got = $t->compile();
        $this->assertEquals($expected, $got);
        $this->assertEquals($expected, $t->getHtml());
    }

}
