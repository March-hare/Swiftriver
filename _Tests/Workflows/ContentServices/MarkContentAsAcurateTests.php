<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class MarkContentAsAcurateTest extends \PHPUnit_Framework_TestCase  {
    public function test() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $workflow = new Workflows\ContentServices\MarkContentAsAcurate();
        $message = $workflow->RunWorkflow('{"id":"e2a8409bae90f5f2484b3356f924e4c6","markerId":"someotherid"}', "somekey");
    }
}
?>
