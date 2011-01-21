<?php
namespace Swiftriver\Core;

require_once 'PHPUnit/Framework.php';

class UpdateContentTaggingTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(dirname(__FILE__)."/../../../Setup.php");
    }

    public function testRunWorkflowWithBadParams()
    {
        $key = "swiftriver_dev";

        $workflow = new Workflows\ContentServices\UpdateContentTagging();

        $this->assertFalse(\strstr($workflow->RunWorkflow(null, $key), "OK"));

        $this->assertFalse(\strstr($workflow->RunWorkflow('{}', $key), "OK"));

        $this->assertFalse(\strstr($workflow->RunWorkflow('{"tagsAdded":[{"text":"one","type":"two"}]}', $key), "OK"));

        $this->assertFalse(\strstr($workflow->RunWorkflow('{"tagsRemoved":[{"text":"one","type":"two"}]}', $key), "OK"));

        $this->assertFalse(\strstr($workflow->RunWorkflow('{"tagsRemoved":[{"text":"one","type":"two"}],"tagsAdded":[{"text":"one","type":"two"}]}', $key), "OK"));

        $this->assertFalse(\strstr($workflow->RunWorkflow('{"id":"test"}', $key), "OK"));
    }

    public function testRunWorkflowWithValidParams()
    {
        $content = new ObjectModel\Content();

        $content->id = "testId1";

        $content->state = "new_content";

        $content->date = time();

        $content->tags = array (
            new ObjectModel\Tag("text1", "type"),
            new ObjectModel\Tag("text2", "type"));

        $source = new ObjectModel\Source();

        $source->id = "testId1";

        $source->parent = "testParentId";

        $source->score = 1;

        $source->name = "testName";

        $source->type = "testType";

        $source->subType = "testSubType";

        $content->source = $source;

        Modules\DataContext\MySql_V2\DataContext::SaveContent(array($content));

        $json = '{"id":"testId1","tagsRemoved":[{"text":"text1","type":"type"},{"text":"text2","type":"type"}],"tagsAdded":[{"text":"text3","type":"type"}]}';

        $workflow = new Workflows\ContentServices\UpdateContentTagging();

        $result = $workflow->RunWorkflow($json, "swiftriver_dev");

        $content = Modules\DataContext\MySql_V2\DataContext::GetContent(array("testId1"));

        $pdo = Modules\DataContext\MySql_V2\DataContext::PDOConnection();

        $pdo->exec("DELETE FROM SC_Content");

        $pdo->exec("DELETE FROM SC_Sources");

        $pdo->exec("DELETE FROM SC_Content_Tags");

        $pdo->exec("DELETE FROM SC_Tags");

        $pdo = null;

        $tags = $content[0]->tags;

        $this->assertEquals(1, \count($tags));

        $this->assertEquals("text3", $tags[0]->text);

        $this->assertEquals("type", $tags[0]->type);
    }
}
?>