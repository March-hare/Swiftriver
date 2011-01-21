<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class GetAllChannelsTest extends \PHPUnit_Framework_TestCase  {
    public function testWithKnownId() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $workflow = new Workflows\ChannelServices\GetAllChannels();
        $result = $workflow->RunWorkflow("swiftriver_dev");
    }
}
