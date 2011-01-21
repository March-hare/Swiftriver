<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class SiCDSPreProcessingStepTests extends \PHPUnit_Framework_TestCase {
    public function testParseToRequestJson() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../Modules/SiCDSInterface/Parser.php");
        include_once(dirname(__FILE__)."/../../../Modules/SiCDSInterface/SiCDSPreProcessingStep.php");
        include_once(dirname(__FILE__)."/../../../Modules/SiCDSInterface/ServiceInterface.php");

        $item = new ObjectModel\Content();
        $item->id = "testId";
        $item->difs = array(
            new ObjectModel\DuplicationIdentificationFieldCollection(
                "names",
                array(
                    new ObjectModel\DuplicationIdentificationField("first", "homer"),
                    new ObjectModel\DuplicationIdentificationField("second", "simpson")
                )
            ),
        );

        $configuration = new MockConfiguration();

        $SiCDS = new \Swiftriver\PreProcessingSteps\SiCDSPreProcessingStep();

        $SiCDS->Process(array($item), $configuration, Setup::GetLogger());

    }
}

class MockConfiguration {
    public $ModulesDirectory;

    public function __construct() {
        $this->ModulesDirectory = dirname(__FILE__)."/../../../Modules";
    }
}

?>