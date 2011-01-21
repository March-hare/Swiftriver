<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class DeactivatePreProcessingStepTests extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\PreProcessingSteps\DeactivatePreProcessingStep();

    }

    public function testWithGoogle() {
        $json = $this->object->RunWorkflow('{"name":"Google Language Services Turbine"}', "swiftriver_dev");
        $this->assertEquals(true, strpos($json, "OK") != 0);
    }
}
?>
