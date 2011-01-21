<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class SourceFacoryTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        include_once(dirname(__FILE__)."/../../../Setup.php");
    }

    public function testCreateSourceFromJson()
    {
        $id = "testId";
        $score = 10;
        $name = "testName";
        $email = "testEmail";
        $link = "http://test.link";
        $parent = "testParent";
        $type = "testType";
        $subType = "testSubType";
        $gisData = array(
            new ObjectModel\GisData(1.1, 1, "test one"),
            new ObjectModel\GisData(2.2, 2, "test two"));

        $source = new ObjectModel\Source();
        $source->id = $id;
        $source->score = $score;
        $source->name = $name;
        $source->email = $email;
        $source->link = $link;
        $source->parent = $parent;
        $source->type = $type;
        $source->subType = $subType;
        $source->gisData = $gisData;

        $json = \json_encode($source);

        $factory = new ObjectModel\ObjectFactories\SourceFactory();

        $s = $factory->CreateSourceFromJSON($json);

        $this->assertEquals($s->id, $id);
        $this->assertEquals($s->name, $name);
        $this->assertEquals($s->score, $score);
        $this->assertEquals($s->email, $email);
        $this->assertEquals($s->link, $link);
        $this->assertEquals($s->parent, $parent);
        $this->assertEquals($s->type, $type);
        $this->assertEquals($s->subType, $subType);
        $this->assertTrue(\is_array($s->gisData));
        $this->assertEquals(2, \count($s->gisData));
        $gis0 = $s->gisData[0];
        $this->assertEquals(1.1, $gis0->longitude);
        $this->assertEquals(1, $gis0->latitude);
        $this->assertEquals("test one", $gis0->name);
        $gis1 = $s->gisData[1];
        $this->assertEquals(2.2, $gis1->longitude);
        $this->assertEquals(2, $gis1->latitude);
        $this->assertEquals("test two", $gis1->name);
    }

    public function testCreateSourceFromIdentifyerWithNewSourceNotTrusted()
    {
        $id = "thisisatest";

        $source = ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromIdentifier($id);

        $this->assertEquals(md5($id), $source->id);

        $this->assertEquals(null, $source->score);
    }

    public function testCreateSourceFromIdentifyerWithNewSourceTrusted()
    {
        $id = "thisisatest";

        $source = ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromIdentifier($id, true);

        $this->assertEquals(md5($id), $source->id);

        $this->assertEquals(100, $source->score);
    }

    public function testCreateSourceFromIdentifyerWithExistingSourceTrusted()
    {
        $db = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $id = md5("testId");

        $db->exec("INSERT INTO SC_Sources VALUES ('$id', 'testParentId', 10, 'testName', 'testType', 'testSubType', '{\"id\":\"$id\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");

        $s = ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromIdentifier("testId", true);

        $db->exec("DELETE FROM SC_Sources");

        $db = null;

        $this->assertEquals($id, $s->id);

        $this->assertEquals(10, $s->score);
    }

    public function testWithProfileImage()
    {
        $json = '{"id":"cfdc9af457caa390bb874b75d98d2a50","date":1290587445,"score":null,"name":"alettieri","email":null,"link":"http:\/\/twitter.com\/alettieri","parent":"7113fda82360580d3bae45b22115fc49","type":"Twitter","subType":"Search","gisData":[],"applicationIds":{"twitter":309775},"applicationProfileImages":{"twitter":"http:\/\/a2.twimg.com\/profile_images\/630747310\/headshot_normal.jpg"}}';

        $source = ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromJSON($json);

        $this->AssertEquals(1, \count($source->applicationProfileImages));

        $this->AssertEquals("http://a2.twimg.com/profile_images/630747310/headshot_normal.jpg", $source->applicationProfileImages["twitter"]);

        $this->AssertEquals(1, \count($source->applicationIds));

        $this->AssertEquals("309775", $source->applicationIds["twitter"]);
    }

}
?>
