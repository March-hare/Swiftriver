<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class MarkContentAsIrreleventTest extends \PHPUnit_Framework_TestCase  {
    public function test() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $workflow = new Workflows\ContentServices\MarkContentAsIrrelevant();
        $message = $workflow->RunWorkflow('{"id":"49ce00cb6f4530823dd1247034b20355","markerId":"someotherid"}', "somekey");
    }
}
?>
