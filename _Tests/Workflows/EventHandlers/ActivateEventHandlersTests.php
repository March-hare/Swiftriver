<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ActivateEventHandlersTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\EventHandlers\ActivateEventHandler();
    }

    public function test() {
        $object->name = "Ushahidi Report Push";
        $json = $this->object->RunWorkflow(json_encode($object), "swiftriver_dev");
    }
}
?>
