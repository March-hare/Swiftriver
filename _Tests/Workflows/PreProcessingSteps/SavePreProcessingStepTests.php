<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class SavePreProcessorStepsTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\PreProcessingSteps\SavePreProcessingStep();
    }

    public function test() {
        $object->name = "SiLCC";
        $object->data = array(
            "Url" => "testUrl"
        );
        $json = $this->object->RunWorkflow(json_encode($object), "swiftriver_dev");
        $this->assertEquals(true, strpos($json, "OK") != 0);
    }
}
?>
