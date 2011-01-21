<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class ParserTests extends \PHPUnit_Framework_TestCase {
    public function testParseToRequestJson() {
        include_once(dirname(__FILE__)."/../../../Modules/SiCDSInterface/Parser.php");
        include_once(dirname(__FILE__)."/../../../Setup.php");

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
        $parser = new \Swiftriver\SiCDSInterface\Parser();
        $json = $parser->ParseToRequestJson(array($item), "testkey");
    }

    public function testParseResponseFromJsonToUniqueIds() {
        include_once(dirname(__FILE__)."/../../../Modules/SiCDSInterface/Parser.php");
        include_once(dirname(__FILE__)."/../../../Setup.php");

        $json = '{"key": "client1","results":[{"id": "item1","result":"unique"}]}';

        $parser = new \Swiftriver\SiCDSInterface\Parser();

        $parser->ParseResponseFromJsonToUniqueIds($json);
    }
}
?>