<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class FeedsParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
    }

    public function testGoogleNewsParser()
    {
        $parser = new Modules\SiSPS\Parsers\GoogleNewsParser();

        $channel = new ObjectModel\Channel();

        $channel->lastSuccess = null;

        $channel->parameters = array("SearchPhrase" => "Ushahidi");

        $content = $parser->GetAndParse($channel);
    }
}
?>
