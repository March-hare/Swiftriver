<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class AnalyticsWorkflowBaseTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(\dirname(__FILE__)."/../../../Setup.php");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseJSONToRequestTypeWithNullJson()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $base->ParseJSONToRequestType(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseJSONToRequestTypeWithInvalidJson()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $base->ParseJSONToRequestType('{"thisis rubbish:}');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseJSONToRequestTypeWithJsonMissingArg()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $base->ParseJSONToRequestType('{"something":"rubbish"}');
    }

    public function testParseJSONToRequestTypeWithValidJson()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $type = $base->ParseJSONToRequestType('{"RequestType":"TestType"}');

        $this->assertEquals("TestType", $type);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseJSONToRequestParametersWithNullJson()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $base->ParseJSONToRequestParameters(null);
    }

    public function testParseJSONToRequestParametersWithValidJson()
    {
        $base = new Workflows\Analytics\AnalyticsWorkflowBase();

        $object = $base->ParseJSONToRequestParameters('{"type":"fun"}');

        $this->assertEquals("fun", $object->type);
    }
}
?>
