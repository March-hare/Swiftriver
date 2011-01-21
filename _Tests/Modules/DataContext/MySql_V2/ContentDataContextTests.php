<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class ContentDataContextTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../../../Setup.php");
        include_once(dirname(__FILE__)."/../../../../Modules/DataContext/MySql_V2/DataContext.php");
    }

    /*
     * SaveContent Tests
     */

    public function testSaveContent()
    {
        $content = new ObjectModel\Content();

        $content->id = "testId1";

        $content->state = "new_content";

        $content->date = time();

        $content->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Content WHERE id = 'testId1'") as $row)
        {
            $found = true;

            $this->assertEquals("testId1", $row["id"]);
        }

        $this->assertEquals(true, $found);

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Sources WHERE id = 'testId1'") as $row)
        {
            $found = true;

            $this->assertEquals("testId1", $row["id"]);
        }

        $this->assertEquals(true, $found);

        $count = 0;

        foreach ($pdo->query("SELECT c.id as id, t.type as type, t.text as text FROM SC_Content c JOIN SC_Content_Tags ct ON c.id = contentId JOIN SC_Tags t ON ct.tagId = t.id WHERE c.id = 'testId1'") as $row)
        {
            $count++;

            $this->assertEquals(true, $row["type"] == "testType1" || $row["type"] == "testType2" );

            $this->assertEquals(true, $row["text"] == "testtext1" || $row["text"] == "testtext2" );
        }

        $this->assertEquals(2, $count);

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;
    }

    public function testSaveContentWithDifferentTags()
    {
        $content = new ObjectModel\Content();

        $content->id = "testId1";

        $content->state = "new_content";

        $content->date = time();

        $content->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        $outArray = Modules\DataContext\MySql_V2\DataContext::GetContent(array("testId1"));

        $this->assertEquals(true, \is_array($outArray));

        $this->assertEquals(1, \count($outArray));

        $this->assertEquals("testId1", $outArray[0]->id);

        $this->assertEquals("new_content", $outArray[0]->state);

        $t = $outArray[0]->tags;

        $this->assertEquals(true, \is_array($t));

        $this->assertEquals("testType1", $t[0]->type);

        $this->assertEquals("testtext1", $t[0]->text);

        $this->assertEquals("testType2", $t[1]->type);

        $this->assertEquals("testtext2", $t[1]->text);

        $s = $outArray[0]->source;

        $this->assertEquals("testId1", $s->id);

        $this->assertEquals("testParentId", $s->parent);

        $this->assertEquals("testName", $s->name);

        $this->assertEquals("testType", $s->type);

        $this->assertEquals("testSubType", $s->subType);

        $content->tags = array (new ObjectModel\Tag("testText1", "testType1"));

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        $outArray2 = Modules\DataContext\MySql_V2\DataContext::GetContent(array("testId1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(true, \is_array($outArray2));

        $this->assertEquals(1, \count($outArray2));

        $this->assertEquals("testId1", $outArray2[0]->id);

        $this->assertEquals("new_content", $outArray2[0]->state);

        $t = $outArray2[0]->tags;

        $this->assertEquals(true, \is_array($t));

        $this->assertEquals(1, \count($t));

        $this->assertEquals("testType1", $t[0]->type);

        $this->assertEquals("testtext1", $t[0]->text);
    }

    /*
     * GetContent Tests
     */

    public function testGetContentWithOneIdAndNoOrderBy()
    {
        $content = new ObjectModel\Content();

        $content->id = "testId1";

        $content->state = "new_content";

        $content->date = time();

        $content->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        $outArray = Modules\DataContext\MySql_V2\DataContext::GetContent(array("testId1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;
        
        $this->assertEquals(true, \is_array($outArray));

        $this->assertEquals(1, \count($outArray));

        $this->assertEquals("testId1", $outArray[0]->id);

        $this->assertEquals("new_content", $outArray[0]->state);

        $t = $outArray[0]->tags;

        $this->assertEquals(true, \is_array($t));

        $this->assertEquals("testType1", $t[0]->type);

        $this->assertEquals("testtext1", $t[0]->text);

        $this->assertEquals("testType2", $t[1]->type);

        $this->assertEquals("testtext2", $t[1]->text);

        $s = $outArray[0]->source;

        $this->assertEquals("testId1", $s->id);

        $this->assertEquals("testParentId", $s->parent);

        $this->assertEquals("testName", $s->name);

        $this->assertEquals("testType", $s->type);

        $this->assertEquals("testSubType", $s->subType);
    }

    public function testGetContentWithTwoIdsAndNoOrderBy()
    {
        $content1 = new ObjectModel\Content();

        $content1->id = "testId1";

        $content1->state = "new_content";

        $content1->date = time();

        $content1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source1 = new ObjectModel\Source();

        $source1->id = "testId1";

        $source1->parent = "testParentId";

        $source1->score = 1;

        $source1->name = "testName";

        $source1->type = "testType";

        $source1->subType = "testSubType";

        $content1->source = $source1;

        $content2 = new ObjectModel\Content();

        $content2->id = "testId2";

        $content2->state = "new_content";

        $content2->date = time();

        $content2->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source2 = new ObjectModel\Source();

        $source2->id = "testId1";

        $source2->parent = "testParentId";

        $source2->score = 1;

        $source2->name = "testName";

        $source2->type = "testType";

        $source2->subType = "testSubType";

        $content2->source = $source2;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content1, $content2));

        $outArray = Modules\DataContext\MySql_V2\DataContext::GetContent(array("testId1", "testId2"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(true, \is_array($outArray));

        $this->assertEquals(2, \count($outArray));

        $this->assertEquals("testId1", $outArray[0]->id);

        $this->assertEquals("new_content", $outArray[0]->state);

        $t = $outArray[0]->tags;

        $this->assertEquals(true, \is_array($t));

        $this->assertEquals("testType1", $t[0]->type);

        $this->assertEquals("testtext1", $t[0]->text);

        $this->assertEquals("testType2", $t[1]->type);

        $this->assertEquals("testtext2", $t[1]->text);

        $s = $outArray[0]->source;

        $this->assertEquals("testId1", $s->id);

        $this->assertEquals("testParentId", $s->parent);

        $this->assertEquals("testName", $s->name);

        $this->assertEquals("testType", $s->type);

        $this->assertEquals("testSubType", $s->subType);

        $this->assertEquals("testId2", $outArray[1]->id);

        $this->assertEquals("new_content", $outArray[1]->state);

        $t = $outArray[1]->tags;

        $this->assertEquals(true, \is_array($t));

        $this->assertEquals("testType1", $t[0]->type);

        $this->assertEquals("testtext1", $t[0]->text);

        $this->assertEquals("testType2", $t[1]->type);

        $this->assertEquals("testtext2", $t[1]->text);

        $s = $outArray[1]->source;

        $this->assertEquals("testId1", $s->id);

        $this->assertEquals("testParentId", $s->parent);

        $this->assertEquals("testName", $s->name);

        $this->assertEquals("testType", $s->type);

        $this->assertEquals("testSubType", $s->subType);
    }

    /*
     * GetContentList
     */

    public function testGetContentListBasic()
    {
        $item1 = new ObjectModel\Content();

        $item1->id = "testId1";

        $item1->state = "new_content";

        $item1->date = time();

        $item1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $item2 = new ObjectModel\Content();

        $item2->id = "testId2";

        $item2->state = "new_content";

        $item2->date = time();

        $item2->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $item1->source = $source;

        $item2->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($item1, $item2));
        
        $result = Modules\DataContext\MySql_V2\DataContext::GetContentList(array());
        
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();
        
        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(2, \count($result["contentItems"]));
    }

    public function testGetContentListWithBasicParams()
    {
        $item1 = new ObjectModel\Content();

        $item1->id = "testId1";

        $item1->state = "new_content";

        $item1->date = time();

        $item1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $item2 = new ObjectModel\Content();

        $item2->id = "testId2";

        $item2->state = "accurate";

        $item2->date = time();

        $item2->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $item1->source = $source;

        $item2->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($item1, $item2));

        $result = Modules\DataContext\MySql_V2\DataContext::GetContentList(array("state" => "accurate"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(true, \is_array($result));

        $this->assertEquals(3, \count($result));

        $this->assertEquals(1, $result["totalCount"]);

        $content = $result["contentItems"];

        $this->assertEquals("testId2", $content[0]->id);
    }

    public function testGetContentListTestNavigation()
    {
        $item1 = new ObjectModel\Content();

        $item1->id = "testId1";

        $item1->state = "new_content";

        $item1->date = time();

        $item1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $item2 = new ObjectModel\Content();

        $item2->id = "testId2";

        $item2->state = "accurate";

        $item2->date = time();

        $item2->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType1";

        $source->subType = "testSubType1";

        $item1->source = $source;

        $source1->id = "testId2";

        $source1->parent = "testParentId";

        $source1->score = 1;

        $source1->name = "testName";

        $source1->type = "testType2";

        $source1->subType = "testSubType2";

        $item1->source = $source;

        $item2->source = $source1;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($item1, $item2));

        $result = Modules\DataContext\MySql_V2\DataContext::GetContentList(array());

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(true, \is_array($result));

        $this->assertEquals(3, \count($result));

        $navigation = $result["navigation"];

        $this->assertEquals(true, \is_array($navigation));

        $this->assertEquals(2, count($navigation));

        $this->assertEquals(true, \is_array($navigation["Channels"]));

        $facets = $navigation["Channels"]["facets"];

        $this->assertEquals(true, \is_array($facets));

        $this->assertEquals(2, \count($facets));

        $this->assertEquals(true, $facets[0]["name"] == "testType1" || $facets[0]["name"] == "testType2");

        $this->assertEquals(true, $facets[1]["name"] == "testType1" || $facets[1]["name"] == "testType2");

        $this->assertEquals(true, \is_array($navigation["Tags"]));

        $facets = $navigation["Tags"]["facets"];

        $this->assertEquals(true, \is_array($facets));

        $this->assertEquals(2, \count($facets));

        $this->assertEquals(true, $facets[0]["name"] == "testtext1" || $facets[0]["name"] == "testtext2");

        $this->assertEquals(true, $facets[1]["name"] == "testtext1" || $facets[1]["name"] == "testtext2");
    }

    public function testGetContentListWithTag()
    {
        $item1 = new ObjectModel\Content();

        $item1->id = "testId1";

        $item1->state = "new_content";

        $item1->date = time();

        $item1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $item2 = new ObjectModel\Content();

        $item2->id = "testId2";

        $item2->state = "accurate";

        $item2->date = time();

        $item2->tags = array (
            new ObjectModel\Tag("testText3", "testType3"),
            new ObjectModel\Tag("testText4", "testType4"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $item1->source = $source;

        $item2->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($item1, $item2));

        $result = Modules\DataContext\MySql_V2\DataContext::GetContentList(array("tag" => "testText1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $this->assertEquals(true, \is_array($result));

        $this->assertEquals(3, \count($result));

        $this->assertEquals(1, $result["totalCount"]);

        $content = $result["contentItems"];

        $this->assertEquals(1, count($content));    
        
        $this->assertEquals("testId1", $content[0]->id);    
    }

    /*
     * DeleteContent Tests
     */
    
    public function testDeleteContentWithOneContent()
    {
        $content = new ObjectModel\Content();

        $content->id = "testId1";

        $content->state = "new_content";

        $content->date = time();

        $content->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        Modules\DataContext\MySql_V2\DataContext::DeleteContent(array($content));
                
        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Content WHERE id = 'testId1'") as $row)
        {
            $found = true;
        }

        $this->assertEquals(false, $found);

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Sources WHERE id = 'testId1'") as $row)
        {
            $found = true;

            $this->assertEquals("testId1", $row["id"]);
        }

        $this->assertEquals(true, $found);

        $count = 0;

        foreach ($pdo->query("SELECT type, text FROM SC_Tags") as $row)
        {
            $count++;

            $this->assertEquals(true, $row["type"] == "testType1" || $row["type"] == "testType2" );

            $this->assertEquals(true, $row["text"] == "testtext1" || $row["text"] == "testtext2" );
        }

        $this->assertEquals(2, $count);

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;
    }

    
    public function testDeleteContentWithTwoContentItems()
    {
        $content1 = new ObjectModel\Content();

        $content1->id = "testId1";

        $content1->state = "new_content";

        $content1->date = time();

        $content1->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $content2 = new ObjectModel\Content();

        $content2->id = "testId2";

        $content2->state = "new_content";

        $content2->date = time();

        $content2->tags = array (
            new ObjectModel\Tag("testText1", "testType1"),
            new ObjectModel\Tag("testText2", "testType2"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content1->source = $source;

        $content2->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content1, $content2));

        Modules\DataContext\MySql_V2\DataContext::DeleteContent(array($content1, $content2));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Content WHERE id in ('testId1', 'testId2')") as $row)
        {
            $found = true;
        }

        $this->assertEquals(false, $found);

        $found = false;

        foreach ($pdo->query("SELECT * FROM SC_Sources WHERE id = 'testId1'") as $row)
        {
            $found = true;

            $this->assertEquals("testId1", $row["id"]);
        }

        $this->assertEquals(true, $found);

        $count = 0;

        foreach ($pdo->query("SELECT type, text FROM SC_Tags") as $row)
        {
            $count++;

            $this->assertEquals(true, $row["type"] == "testType1" || $row["type"] == "testType2" );

            $this->assertEquals(true, $row["text"] == "testtext1" || $row["text"] == "testtext2" );
        }

        $this->assertEquals(2, $count);

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;
    }
}
?>
