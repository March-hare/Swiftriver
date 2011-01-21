<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class TwitterParserTest extends \PHPUnit_Framework_TestCase {
    /**
     * Include the SiSPS Setup
     */
    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
    }

    public function testTwitterSearch() {
        $source = new ObjectModel\Source();
        $source->subType = "Search";
        $source->type = "Twitter";
        $source->lastSuccess = strtotime("-1 month");
        $source->parameters = array(
            "SearchKeyword" => "#bp"
        );
        $parser = new Modules\SiSPS\Parsers\TwitterParser();
        $content = $parser->GetAndParse($source);
    }
    
    public function testTwitterAccount() {
        $source = new ObjectModel\Source();
        $source->subType = "Follow User";
        $source->parameters = array(
            "TwitterAccount" => "jongos"
        );
        $parser = new Modules\SiSPS\Parsers\TwitterParser();
        $content = $parser->GetAndParse($source);
    }
}
?>
