<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class GetContent extends \PHPUnit_Framework_TestCase  {
    private $object;

    protected function setUp() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $this->object = new Workflows\ContentServices\GetContent();
    }

    public function testWithJustState() {
        $content = $this->object->RunWorkflow(
                        json_encode(array("state" => "new_content"))
                        , null);
    }

    public function testWithPagination() {
        $content = $this->object->RunWorkflow(
                        json_encode(array("state" => "new_content", "pageStart" => 0, "pageSize" => 20))
                        , null);
    }

    public function testWithVeracity() {
        $content = $this->object->RunWorkflow(
                        json_encode(array("state" => "new_content", "minVeracity" => 0, "maxVeracity" => 20))
                        , null);
    }
}
?>
