<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class SourceDataContextTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../../Modules/DataContext/MySql_V2/DataContext.php");
    }

    /*
     * GetSourceById test
     */

    public function testGetSourceByIdWithNoIds()
    {
        $sources = Modules\DataContext\MySql_V2\DataContext::GetSourcesById(array());

        $this->assertEquals(true, \is_array($sources));

        $this->assertEquals(0, \count($sources));
    }

    public function testGetSourceByIdWithOneValidId()
    {
        $db = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $db->exec("INSERT INTO SC_Sources VALUES ('testId1', 'testParentId', 100, 10, 'testName', 'testType', 'testSubType', '{\"id\":\"testId1\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");

        $sources = Modules\DataContext\MySql_V2\DataContext::GetSourcesById(array("testId1"));

        $db->exec("DELETE FROM SC_Sources");

        $db = null;

        $this->assertEquals(true, \is_array($sources));

        $this->AssertEquals(1, \count($sources));

        $this->assertEquals("testId1", $sources[0]->id);

        $this->assertEquals("testParentId", $sources[0]->parent);

        $this->assertEquals(10, $sources[0]->score);

        $this->assertEquals("testName", $sources[0]->name);

        $this->assertEquals("testType", $sources[0]->type);

        $this->assertEquals("testSubType", $sources[0]->subType);
    }

    public function testGetSourceByIdWithTwoValidId()
    {
        $db = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $db->exec("INSERT INTO SC_Sources VALUES ('testId1', 'testParentId', 100, 10, 'testName', 'testType', 'testSubType', '{\"id\":\"testId1\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");

        $db->exec("INSERT INTO SC_Sources VALUES ('testId2', 'testParentId', 100, 10, 'testName', 'testType', 'testSubType', '{\"id\":\"testId2\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");

        $sources = Modules\DataContext\MySql_V2\DataContext::GetSourcesById(array("testId1", "testId2"));

        $db->exec("DELETE FROM SC_Sources");

        $db = null;

        $this->assertEquals(true, \is_array($sources));

        $this->AssertEquals(2, \count($sources));

        $this->assertEquals("testId1", $sources[0]->id);

        $this->assertEquals("testParentId", $sources[0]->parent);

        $this->assertEquals(10, $sources[0]->score);

        $this->assertEquals("testName", $sources[0]->name);

        $this->assertEquals("testType", $sources[0]->type);

        $this->assertEquals("testSubType", $sources[0]->subType);

        $this->assertEquals("testId2", $sources[1]->id);

        $this->assertEquals("testParentId", $sources[1]->parent);

        $this->assertEquals(10, $sources[1]->score);

        $this->assertEquals("testName", $sources[1]->name);

        $this->assertEquals("testType", $sources[1]->type);

        $this->assertEquals("testSubType", $sources[1]->subType);
    }

    /*
     * LstAllSource Test
     */
    
    public function testListAllSources()
    {
        $db = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $db->exec("INSERT INTO SC_Sources VALUES ('testId1', 'testParentId', 100, 10, 'testName', 'testType', 'testSubType', '{\"id\":\"testId1\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");
        
        $db->exec("INSERT INTO SC_Sources VALUES ('testId2', 'testParentId', 100, 10, 'testName', 'testType', 'testSubType', '{\"id\":\"testId2\",\"score\":10,\"name\":\"testName\",\"parent\":\"testParentId\",\"email\":null,\"link\":\"http:\/\/twitter.com\/datninja12\",\"type\":\"testType\",\"subType\":\"testSubType\"}')");
        
        $sources = Modules\DataContext\MySql_V2\DataContext::ListAllSources();

        $db->exec("DELETE FROM SC_Sources");

        $db = null;

        $this->assertEquals(true, \is_array($sources));

        $this->AssertEquals(2, \count($sources));

        $this->assertEquals("testId1", $sources[0]->id);

        $this->assertEquals("testParentId", $sources[0]->parent);

        $this->assertEquals(10, $sources[0]->score);

        $this->assertEquals("testName", $sources[0]->name);

        $this->assertEquals("testType", $sources[0]->type);

        $this->assertEquals("testSubType", $sources[0]->subType);

        $this->assertEquals("testId2", $sources[1]->id);

        $this->assertEquals("testParentId", $sources[1]->parent);

        $this->assertEquals(10, $sources[1]->score);

        $this->assertEquals("testName", $sources[1]->name);

        $this->assertEquals("testType", $sources[1]->type);

        $this->assertEquals("testSubType", $sources[1]->subType);
    }
}
?>
