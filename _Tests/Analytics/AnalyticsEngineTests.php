<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class AnalyticsEngineTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../Setup.php");
    }

    public function testWithNoProviders()
    {
        $engine = new Analytics\AnalyticsEngine();
    }

    public function testWithMockAnalyticsProvider()
    {
        $request = new Analytics\AnalyticsRequest();

        $request->RequestType = "MockAnalyticsProvider";

        $engine = new Analytics\AnalyticsEngine(dirname(__FILE__));

        $result = $engine->RunAnalyticsRequest($request);

        $this->assertEquals(1, $result->Result);
    }
}
?>
