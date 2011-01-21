<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ListAvailablePreProcessorStepsTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\PreProcessingSteps\ListAllPreProcessingSteps();

    }

    public function test() {
        $json = $this->object->RunWorkflow("swiftriver_dev");
    }
}
?>
