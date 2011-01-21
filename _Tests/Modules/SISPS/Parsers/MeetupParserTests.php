<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class MeetupParserTest extends \PHPUnit_Framework_TestCase {
    /**
     * Include the SiSPS Setup
     */
    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
    }

    public function test()
    {
        $parser = new Modules\SiSPS\Parsers\MeetupParser();

        $channel->parameters = array (
            "APIKey" => "5135594e71494b42724818d15276122",
            "urlname" => "red");

        $channel->type = $parser->ReturnType();

        $channel->subType = "Meetup Everywhere Event Search";

        $channel->lastSuccess = time();

        $content = $parser->GetAndParse($channel);
    }
}
?>
