<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ListAvailablePreProcessorStepsTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\PreProcessingSteps\ActivatePreProcessingStep();

    }

    public function testWithRubish() {
        $json = $this->object->RunWorkflow(
                '{"name":"rubish"}',
                "swiftriver_dev");
        $this->assertEquals(true, strpos($json, "OK") == 0);
    }

    public function testWithGoogle() {
        $json = $this->object->RunWorkflow('{"name":"Google Language Services Turbine"}', "swiftriver_dev");
        $this->assertEquals(true, strpos($json, "OK") != 0);
    }
}
?>
