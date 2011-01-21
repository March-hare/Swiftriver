<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class DynamicModuleConfigurationHandlerTest extends \PHPUnit_Framework_TestCase {
    public function testWithMockConfig() {
        include_once(dirname(__FILE__)."/../../../Setup.php");
        $config = new Configuration\ConfigurationHandlers\DynamicModuleConfigurationHandler(dirname(__FILE__)."/MockDynamicModuleConfigurationFile.xml");
        $configuration = $config->Configuration["test"];
        if($configuration == null){
            $configuration = array();
        }
        $property = null;
        foreach($configuration as $key => $prop) {
            if($prop->name == "testprop") {
                $property = $prop;
            }
        }
        if($property == null) {
            $property = new ObjectModel\ConfigurationElement(
                    "testprop",
                    "string",
                    "just a test this is!",
                    "testvalue");
        } else {
            $property->value = "changed value";
        }
        $configuration[$property->name] = $property;
        $config->Configuration["test"] = $configuration;
        $config->Save();
    }
}
?>
