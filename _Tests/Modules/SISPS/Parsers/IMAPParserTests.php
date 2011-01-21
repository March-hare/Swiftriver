<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class IAMPParserTest extends \PHPUnit_Framework_TestCase {
    
    private $gmailUserName = null;
    
    private $gmailPassword = null;

    /**
     * Include the SiSPS Setup
     */
    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
    }

    public function testGmailSubType() {
        
        if($this->gmailUserName == null)
            return;

        $channel->lastSuccess = strtotime(date("c") . "-1 day");//\time();
        $channel->subType = "Gmail";
        $channel->parameters = array (
            "UserName" => $this->gmailUserName,
            "Password" => $this->gmailPassword);
        $parser = new Modules\SiSPS\Parsers\IMAPParser();
        $content = $parser->GetAndParse($channel);
    }
}
?>
