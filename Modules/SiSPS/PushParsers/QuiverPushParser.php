<?php
namespace Swiftriver\Core\Modules\PushParsers;
class QuiverPushParser implements IPushParser
{
    /**
     * Implementation of IPushParser::PushAndParse
     * @param $raw_content
     * @param $post_content
     * @param $get_content
     * @return \Swiftriver\Core\ObjectModel\Content[] contentItems
     */
    public function PushAndParse($raw_content = null, $post_content = null, $get_content = null)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Modules::SiSPS::PushParsers::QuiverParser::PushAndParse [Method invoked]", \PEAR_LOG_DEBUG);

        $logger->log("Core::Modules::SiSPS::PushParsers::QuiverParser::PushAndParse [START: Extracting required parameters]", \PEAR_LOG_DEBUG);

        $settings = $this->get_settings();

        $source_name = $this->ReturnType();
        $source = \Swiftriver\Core\ObjectModel\ObjectFactories\SourceFactory::CreateSourceFromIdentifier($source_name, $settings["trusted"]);
        $source->name = $source_name;
        $source->link = $post_content["link"];
        $source->type = $this->ReturnType();
        $source->subType = $this->ReturnType();

        //Create a new Content item
        $item = \Swiftriver\Core\ObjectModel\ObjectFactories\ContentFactory::CreateContent($source);

        //Fill the Content Item
        $item->text[] = new \Swiftriver\Core\ObjectModel\LanguageSpecificText(
                null, //here we set null as we dont know the language yet
                $post_content["title"],
                array($post_content["description"]));
        $item->link = $post_content["link"];
        $item->date = time();

        //Add the item to the Content array
        $contentItems[] = $item;


        //return the content array
        return $contentItems;
    }

    private function get_settings() {
        return array("trusted" => true);
    }

    /**
     * This method returns a string describing the type of sources
     * it can parse. For example, the FeedsParser returns "Feeds".
     *
     * @return string type of sources parsed
     */
    public function ReturnType()
    {
        return "Quiver";
    }
}
?>