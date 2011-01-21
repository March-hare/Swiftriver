<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ContentFacoryTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        include_once(dirname(__FILE__)."/../../../Setup.php");
    }

    public function testCreateContentFromJson()
    {
        $c = new ObjectModel\Content();

        $c->gisData = array (
            new ObjectModel\GisData(-100, 100, "TestOne"),
            new ObjectModel\GisData(100, -100, "TestTwo"));

        $json = \json_encode($c);

        $c = null;

        $factory = new ObjectModel\ObjectFactories\ContentFactory();

        $c = $factory->CreateContent(null, $json);

    }

    public function testCreateSourceFromIdentifyerWithNewSourceNotTrusted()
    {
        $id = "thisisatest";

        $source = ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromIdentifier($id);

        $this->assertEquals(md5($id), $source->id);

        $this->assertEquals(null, $source->score);
    }
}
?>
