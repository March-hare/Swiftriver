<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ContentByChannelOverTimeAnalyticsProviderTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../Modules/BasicAnalyticsProviders/ContentByChannelOverTimeAnalyticsProvider.php");
    }

    public function test()
    {
        $request = new Analytics\AnalyticsRequest();

        $request->DataContextType = "\Swiftriver\Core\Modules\DataContext\MySql_V2\DataContext";

        $request->Parameters = array("TimeLimit" => 6);

        $request->RequestType = "ContentByChannelOverTimeAnalyticsProvider";

        $engine = new Analytics\AnalyticsEngine();

        $result = $engine->RunAnalyticsRequest($request);
    }
}
?>
