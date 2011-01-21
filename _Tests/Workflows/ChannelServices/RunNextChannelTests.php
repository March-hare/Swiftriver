<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class RunNextChannelTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\ChannelServices\RunNextChannel();

    }

    public function test() {
        $this->object->RunWorkflow("swiftriver_dev");
    }
}
?>
