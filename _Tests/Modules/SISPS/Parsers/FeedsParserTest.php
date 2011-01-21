<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class FeedsParserTest extends \PHPUnit_Framework_TestCase {
    /**
     * Include the SiSPS Setup
     */
    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
    }

    /**
     * Tests that given the an array of parameters containing a
     * valid feedUrl, the RSSParser can correctly extract an
     * array of content Items.
     */
    public function testThatTheRSSParserCanExtractContentItemsFromTheAppfricaBlog() {
        $parser = new \Swiftriver\Core\Modules\SiSPS\Parsers\FeedsParser();
        $channel->parameters = array(
            "feedUrl" => "http://feeds.feedburner.com/Appfrica?format=xml"
        );
        
        $content = $parser->GetAndParse($channel);
        $this->assertEquals(true, isset($content));
        $this->assertEquals(true, is_array($content));
        foreach($content as $item) {
            $this->assertEquals(true, isset($item));
            $title = $item->title;
            $link = $item->link;
            $text = reset($item->text);
            $this->assertEquals(true, isset($text->title));
            $this->assertEquals(true, isset($link));
            $this->assertEquals(true, isset($text->text));
            $this->assertEquals(true, is_array($text->text));
        }
    }
}
?>
