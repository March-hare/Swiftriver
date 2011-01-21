<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ChannelDataContextTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../../Modules/DataContext/MySql_V2/DataContext.php");
    }

    /*
     * GetChannelsById Tests
     */

    public function testGetChannelsByIdWithInvalidArgs()
    {
        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array());

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(null);

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));
    }

    public function testGetChannelsByIdWithWrongId()
    {
        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("some-madeup-id"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));
    }

    public function testGetChannelsByIdWithGoodId()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId', '', '', 1, 0, 1, '{\"id\":\"testId\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId"));

        $pdo->exec("DELETE FROM SC_Channels WHERE id = 'testId'");

        $pdo = null;

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(1, count($channels));

        $this->assertEquals("testId", $channels[0]->id);
    }

    public function testGetChannelsByIdWithTwoGoodId()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId1', '', '', 1, 0, 1, '{\"id\":\"testId1\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId2', '', '', 1, 0, 1, '{\"id\":\"testId2\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1", "testId2"));

        $pdo->exec("DELETE FROM SC_Channels WHERE id in ('testId1', 'testid2')");

        $pdo = null;

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(2, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("testId2", $channels[1]->id);
    }

    /*
     * SaveChannels Tests
     */

    public function testSaveChannelsWithOneNewChannel()
    {
        $channel = new ObjectModel\Channel();

        $channel->id = "testId1";

        $channel->type = "test";

        $channel->subType = "test";

        $channel->active = false;

        $channel->inprocess = true;

        $channel->nextrun = 100;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Channels WHERE id = 'testId1'");

        $pdo = null;

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(1, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("test", $channels[0]->type);

        $this->assertEquals("test", $channels[0]->subType);

        $this->assertEquals(false, $channels[0]->active);

        $this->assertEquals(true, $channels[0]->inprocess);

        $this->assertEquals(100, $channels[0]->nextrun);
    }

    public function testSaveChannelWithOneUpdatedChannel()
    {
        $channel = new ObjectModel\Channel();

        $channel->id = "testId1";

        $channel->type = "test";

        $channel->subType = "test";

        $channel->active = false;

        $channel->inprocess = true;

        $channel->nextrun = 100;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(1, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("test", $channels[0]->type);

        $this->assertEquals("test", $channels[0]->subType);

        $this->assertEquals(false, $channels[0]->active);

        $this->assertEquals(true, $channels[0]->inprocess);

        $this->assertEquals(100, $channels[0]->nextrun);

        $channel->type = "test1";

        $channel->subType = "test1";

        $channel->active = true;

        $channel->inprocess = false;

        $channel->nextrun = 101;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Channels WHERE id = 'testId1'");

        $pdo = null;

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(1, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("test1", $channels[0]->type);

        $this->assertEquals("test1", $channels[0]->subType);

        $this->assertEquals(true, $channels[0]->active);

        $this->assertEquals(false, $channels[0]->inProcess);

        $this->assertEquals(101, $channels[0]->nextrun);
    }

    public function testSaveChannelWithTwoNewChannels()
    {
        $channel1 = new ObjectModel\Channel();

        $channel1->id = "testId1";

        $channel1->type = "test";

        $channel1->subType = "test";

        $channel1->active = false;

        $channel1->inprocess = true;

        $channel1->nextrun = 100;

        $channel2 = new ObjectModel\Channel();

        $channel2->id = "testId2";

        $channel2->type = "test2";

        $channel2->subType = "test2";

        $channel2->active = true;

        $channel2->inprocess = false;

        $channel2->nextrun = 101;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel1, $channel2));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1", "testId2"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Channels WHERE id in ('testId1', 'testId2')");

        $pdo = null;

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(2, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("test", $channels[0]->type);

        $this->assertEquals("test", $channels[0]->subType);

        $this->assertEquals(false, $channels[0]->active);

        $this->assertEquals(true, $channels[0]->inprocess);

        $this->assertEquals(100, $channels[0]->nextrun);

        $this->assertEquals("testId2", $channels[1]->id);

        $this->assertEquals("test2", $channels[1]->type);

        $this->assertEquals("test2", $channels[1]->subType);

        $this->assertEquals(true, $channels[1]->active);

        $this->assertEquals(false, $channels[1]->inprocess);

        $this->assertEquals(101, $channels[1]->nextrun);
    }

    /*
     * RemoveChannels Tests
     */

    public function testRemoveChannelsWithOneChannel()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId', '', '', 1, 0, 1, '{\"id\":\"testId\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $pdo = null;

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(1, count($channels));

        $this->assertEquals("testId", $channels[0]->id);

        Modules\DataContext\MySql_V2\DataContext::RemoveChannels(array("testId"));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));
    }

    public function testRemoveChannelsWithTwoChannels()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId1', '', '', 1, 0, 1, '{\"id\":\"testId1\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId2', '', '', 1, 0, 1, '{\"id\":\"testId2\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $pdo = null;

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1", "testId2"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(2, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("testId2", $channels[1]->id);

        Modules\DataContext\MySql_V2\DataContext::RemoveChannels(array("testId1", "testId2"));

        $channels = Modules\DataContext\MySql_V2\DataContext::GetChannelsById(array("testId1", "testId2"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));
    }

    /*
     * SelectNextDueChannel Tests
     */
    
    public function testSelectNextDueChannelBasic()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $time = time();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId1', '', '', 1, 0, $time, '{\"id\":\"testId1\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $channel = Modules\DataContext\MySql_V2\DataContext::SelectNextDueChannel($time);
        
        $this->assertEquals(true, isset($channel));
        
        $this->assertEquals(false, $channel == null);
        
        $this->assertEquals("testId1", $channel->id);

        $pdo->exec("DELEET FROM SC_Channels WHERE id = 'testId1'");

        $pdo = null;
    }

    public function testSelectNextDueChannelWithNoChannels()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Channels");

        $pdo = null;

        $channel = Modules\DataContext\MySql_V2\DataContext::SelectNextDueChannel(null);

        $this->assertEquals(null, $channel);
    }

    public function testSelectNextDueChannelWithTwoChannels()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $time = time();

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId1', '', '', 1, 0, $time, '{\"id\":\"testId1\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $secondTime = $time - 100;

        $pdo->exec("INSERT INTO SC_Channels VALUES ('testId2', '', '', 1, 0, $secondTime, '{\"id\":\"testId2\",\"name\":\"Stupit search\",\"type\":\"Twitter\",\"subType\":\"Search\",\"parameters\":{\"SearchKeyword\":\"hjdkfhsdkjfhsdkjhfsdkh\"},\"updatePeriod\":1,\"nextrun\":1280953609,\"lastrun\":null,\"lastSucess\":null,\"inprocess\":false,\"timesrun\":0,\"active\":true,\"deleted\":true}')");

        $channel = Modules\DataContext\MySql_V2\DataContext::SelectNextDueChannel($time);

        $this->assertEquals(true, isset($channel));

        $this->assertEquals(false, $channel == null);

        $this->assertEquals("testId2", $channel->id);

        $pdo->exec("DELEET FROM SC_Channels WHERE id in ('testId1', 'testId2')");

        $pdo = null;
    }

    public function testSelectNextDueChannelWithTwoChannelsAndActiveFlag()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $time = time();

        $channel = new ObjectModel\Channel();

        $channel->id = "testId1";

        $channel->type = "test";

        $channel->subType = "test";

        $channel->active = true;

        $channel->inprocess = false;

        $channel->nextrun = $time;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channel->id = "testId2";

        $channel->active = false;

        $channel->nextrun = $time - 100;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channel = Modules\DataContext\MySql_V2\DataContext::SelectNextDueChannel($time);

        Modules\DataContext\MySql_V2\DataContext::RemoveChannels(array("testId1", "testId2"));

        $this->assertEquals(true, isset($channel));

        $this->assertEquals(false, $channel == null);

        $this->assertEquals("testId1", $channel->id);
    }

    public function testSelectNextDueChannelWithTwoChannelsAndInProcessFlag()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $time = time();

        $channel = new ObjectModel\Channel();

        $channel->id = "testId1";

        $channel->type = "test";

        $channel->subType = "test";

        $channel->active = true;

        $channel->inprocess = false;

        $channel->nextrun = $time;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channel->id = "testId2";

        $channel->inprocess = true;

        $channel->nextrun = $time - 100;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channel = Modules\DataContext\MySql_V2\DataContext::SelectNextDueChannel($time);

        Modules\DataContext\MySql_V2\DataContext::RemoveChannels(array("testId1", "testId2"));

        $this->assertEquals(true, isset($channel));

        $this->assertEquals(false, $channel == null);

        $this->assertEquals("testId1", $channel->id);
    }

    /*
     * ListAllChannels Tests
     */

    public function testListAllChannelsWithNoChannels()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Channels");

        $pdo = null;

        $channels = Modules\DataContext\MySql_V2\DataContext::ListAllChannels();

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(0, count($channels));
    }

    public function testListAllChannelsWithTwoChannelsAndActiveAndInProcessFlags()
    {
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $time = time();

        $channel = new ObjectModel\Channel();

        $channel->id = "testId1";

        $channel->type = "test";

        $channel->subType = "test";

        $channel->active = true;

        $channel->inprocess = true;

        $channel->nextrun = $time;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channel->id = "testId2";

        $channel->active = false;

        $channel->nextrun = $time - 100;

        Modules\DataContext\MySql_V2\DataContext::SaveChannels(array($channel));

        $channels = Modules\DataContext\MySql_V2\DataContext::ListAllChannels();

        Modules\DataContext\MySql_V2\DataContext::RemoveChannels(array("testId1", "testId2"));

        $this->assertEquals(true, is_array($channels));

        $this->assertEquals(2, count($channels));

        $this->assertEquals("testId1", $channels[0]->id);

        $this->assertEquals("testId2", $channels[1]->id);
    }
}
?>