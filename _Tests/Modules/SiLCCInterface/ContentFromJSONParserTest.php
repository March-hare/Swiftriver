<?php
namespace Swiftriver\SiLCCInterface;
require_once 'PHPUnit/Framework.php';

class ContentFromJSONParserTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../Modules/SiLCCInterface/ContentFromJSONParser.php");
    }

    public function testWithNullContentItems() {
        $parser = new ContentFromJSONParser(null, "");
        $contentItems = $parser->GetTaggedContent();
        $this->assertEquals(null, $contentItems);
    }

    public function testWithNullJSON() {
        $content = new \Swiftriver\Core\ObjectModel\Content();
        $content->id = "testId";
        $parser = new ContentFromJSONParser($content, null);
        $contentItem = $parser->GetTaggedContent();
        $this->assertEquals(true, isset($contentItem));
        $this->assertEquals("testId", $contentItem->id);
    }

    public function testWithEmptyJSON() {
        $content = new \Swiftriver\Core\ObjectModel\Content();
        $content->id = "testId";
        $parser = new ContentFromJSONParser($content, "");
        $contentItem = $parser->GetTaggedContent();
        $this->assertEquals(true, isset($contentItem));
        $this->assertEquals("testId", $contentItem->id);
    }

    public function testWithBADJSON() {
        $content = new \Swiftriver\Core\ObjectModel\Content();
        $content->id = "testId";
        $parser = new ContentFromJSONParser($content, '[{"this is":bad json, well i think it is]}]');
        $contentItem = $parser->GetTaggedContent();
        $this->assertEquals(true, isset($contentItem));
        $this->assertEquals("testId", $contentItem->id);
    }

    public function testWithFullGoodJSON() {
        $content = new \Swiftriver\Core\ObjectModel\Content();
        $content->id = "testId";
        $json = '["tagone","tagtwo"]';
        $parser = new ContentFromJSONParser($content, $json);
        $item = $parser->GetTaggedContent();
        $this->assertEquals(2, count($item->tags));
        $tags = $item->tags;
        $firsttag = $tags[0];
        $this->assertEquals("General", $firsttag->type);
        $this->assertEquals("tagone", $firsttag->text);
        $lasttag = $tags[1];
        $this->assertEquals("General", $lasttag->type);
        $this->assertEquals("tagtwo", $lasttag->text);
    }

}
?>
