<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class ActivateChannelTest extends \PHPUnit_Framework_TestCase  {
    public function testWithKnownId() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $workflow = new Workflows\ChannelServices\ActivateChannel();
        $result = $workflow->RunWorkflow('{"id":"c1891269fa0f60849534b40b62a1c964"}', "swiftriver_dev");
    }
}
