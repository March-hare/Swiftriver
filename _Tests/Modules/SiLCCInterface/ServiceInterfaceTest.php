<?php
namespace Swiftriver\SiLCCInterface;
require_once 'PHPUnit/Framework.php';

class ServiceInterfaceTest extends \PHPUnit_Framework_TestCase {
    public function test() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../Modules/SiLCCInterface/SiLCCPreProcessingStep.php");
        $service = new ServiceInterface();
        $uri = "http://opensilcc.com/api/tag";
        $text = urlencode("In 1972, a crack commando unit was sent to prison by a military court for a crime they didn't commit. They promptly escaped from a maximum security stockade to the Los Angeles underground. Today, still wanted by the government, they survive as soldiers of fortune. If you have a problem, if no-one else can help, and if you can find them, maybe you can hire the A-Team.");
        $config = \Swiftriver\Core\Setup::COnfiguration();
        $json = $service->InterafceWithService($uri, $text, $config);
        $this->assertEquals(
                '["crack", "commando", "unit", "prison", "court", "crime", "commit", "maximum", "security", "stockade", "Los", "Angeles", "underground", "Today", "government", "soldier", "fortune", "problem", "A-Team"]',
                $json
        );
    }
}
?>
