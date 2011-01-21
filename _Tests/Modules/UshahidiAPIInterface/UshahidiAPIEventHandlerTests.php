<?php
namespace Swiftriver\UshahidiAPIInterface;
require_once 'PHPUnit/Framework.php';

class UshahidiAPIEventHandlerTests extends \PHPUnit_Framework_TestCase {
    public function test() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../Modules/UshahidiAPIInterface/UshahidiAPIEventHandler.php");

        $event->arguments = new \Swiftriver\Core\ObjectModel\Content();

        $configuration->ModulesDirectory = dirname(__FILE__) . "/../../../Modules/";

        $handler = new \Swiftriver\EventHandlers\UshahidiAPIEventHandler();

        $handler->HandleEvent($event, $configuration, \Swiftriver\Core\Setup::GetLogger());
    }
}
?>