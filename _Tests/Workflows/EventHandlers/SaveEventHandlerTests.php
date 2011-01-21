<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class SaveEventHandlerTest extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\EventHandlers\SaveEventHandler();
    }

    public function test() {
        $object->name = "Ushahidi Report Push";
        $object->data = array(
            "Ushahidi Url" => "testUrl"
        );
        $json = $this->object->RunWorkflow(json_encode($object), "swiftriver_dev");
    }
}
?>
