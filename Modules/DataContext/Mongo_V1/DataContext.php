<?php
namespace Swiftriver\Core\Modules\DataContext\Mongo_V1;
/**
 * @author am[at]swiftly[dot]org
 */
class DataContext implements
     \Swiftriver\Core\DAL\DataContextInterfaces\IAPIKeyDataContext,
     \Swiftriver\Core\DAL\DataContextInterfaces\IChannelDataContext,
     \Swiftriver\Core\DAL\DataContextInterfaces\IContentDataContext,
     \Swiftriver\Core\DAL\DataContextInterfaces\ISourceDataContext,
     \Swiftriver\Core\DAL\DataContextInterfaces\ITrustLogDataContext
{
    /**
     * Generic function used to gain a new PDO connection to
     * the database.
     *
     * @return \PDO
     */

    public static function MongoDatabase()
    {
        $host = (string) Setup::$Configuration->Host;
        $port = (string) Setup::$Configuration->Port;
        $user = (string) Setup::$Configuration->User;
        $database = (string) Setup::$Configuration->Database;
        $password = (string) Setup::$Configuration->Password;
        $persist = (string) Setup::$Configuration->Persist;
        $persist_key = (string) Setup::$Configuration->PersistKey;

        $mongo_db = new \Swiftriver\Core\Modules\DataContext\Mongo_V1\Mongo_db($host, $port, $user,
            $password, $database, $persist, $persist_key);

        return $mongo_db;
    }

    /**
     * Checks that the given API Key is registed for this
     * Core install
     * @param string $key
     * @return bool
     */
    public static function IsRegisterdCoreAPIKey($key)
    {

    }

    /**
     * Given a new APIKey, this method adds it to the
     * data store or registered API keys.
     * Returns true on sucess
     *
     * @param string $key
     * @return bool
     */
    public static function AddRegisteredCoreAPIKey($key)
    {

    }

    /**
     * Given an APIKey, this method will remove it from the
     * data store of registered API Keys
     * Returns true on sucess
     *
     * @param string key
     * @return bool
     */
    public static function RemoveRegisteredCoreAPIKey($key)
    {

    }

    /**
     * Given the IDs of Channels, this method
     * gets them from the underlying data store
     *
     * @param string[] $ids
     * @return \Swiftriver\Core\ObjectModel\Channel[]
     */
    public static function GetChannelsById($ids)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $db = self::MongoDatabase();

        $channels = array();

        if(!\is_array($ids) || count($ids) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetChannelsById [No ids supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetChannelsById [Method finished]", \PEAR_LOG_DEBUG);

            $id_array = array();

            foreach($ids as $id) {
                $id_array[] = $id;
            }

            $db->where_in('id', $id_array);
            $returned_channels = $db->get('channels');

            foreach($returned_channels as $channel) {
                $channels[] = (object)$channel;
            }
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetChannelsById [START: Building queries]", \PEAR_LOG_DEBUG);

        return $channels;
    }

    /**
     * Adds a list of new Channels to the data store
     *
     * @param \Swiftriver\Core\ObjectModel\Channel[] $Channels
     */
    public static function SaveChannels($channels)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [Method Invoked]", \PEAR_LOG_DEBUG);

        $db = self::MongoDatabase();

        if(!\is_array($channels) || count($channels) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [No channels supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [Method finished]", \PEAR_LOG_DEBUG);

            return;
        }

        try
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [START: Looping through channels]", \PEAR_LOG_DEBUG);

            foreach($channels as $channel)
            {
                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [START: Executing PDO statement for channel]", \PEAR_LOG_DEBUG);

                $exists_result = $db->get_where("channels", array("id" => $channel->id));

                $result = null;

                if(count($exists_result) > 0) {
                    $db->where("id", $channel->id);

                    $result = $db->update("channels", array("name" => $channel->name,
                        "type" => $channel->type,
                        "subType" => $channel->subType,
                        "active" => $channel->active,
                        "inprocess" => $channel->inprocess,
                        "nextRun" => $channel->nextrun,
                        "timesrun" => $channel->timesrun,
                        "updatePeriod" => $channel->updatePeriod,
                        "lastSuccess" => $channel->lastSuccess,
                        "deleted" => $channel->deleted,
                        "trusted" => $channel->trusted,
                        "parameters" => $channel->parameters));

                    $db->where(array());
                }
                else {
                    $result = $db->insert("channels", array("id" => $channel->id,
                        "name" => $channel->name,
                        "type" => $channel->type,
                        "subType" => $channel->subType,
                        "active" => $channel->active,
                        "inprocess" => $channel->inprocess,
                        "nextRun" => $channel->nextrun,
                        "timesrun" => $channel->timesrun,
                        "updatePeriod" => $channel->updatePeriod,
                        "lastSuccess" => $channel->lastSuccess,
                        "deleted" => $channel->deleted,
                        "trusted" => $channel->trusted,
                        "parameters" => $channel->parameters));
                }

                if($result === FALSE)
                {
                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [An Exception was thrown by the Mongo DB framwork]", \PEAR_LOG_ERR);

                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [Could not insert a new channel into the Mongo Collection]", \PEAR_LOG_ERR);
                }

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [END: Executing PDO statement for channel]", \PEAR_LOG_DEBUG);
            }

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [END: Looping through channels]", \PEAR_LOG_DEBUG);

            $db = null;
        }
        catch(\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveChannels [$e]", \PEAR_LOG_ERR);
        } 
    }

    /**
     * Given a list of IDs this method removes the Channels from
     * the data store.
     *
     * @param string[] $ids
     */
    public static function RemoveChannels($ids)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [Method Invoked]", \PEAR_LOG_DEBUG);

        $db = self::MongoDatabase();

        if(!\is_array($ids) || count($ids) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [No ids supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [Method finished]", \PEAR_LOG_DEBUG);

            return;
        }

        try
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [START: Looping through ids]", \PEAR_LOG_DEBUG);

            foreach($ids as $id)
            {
                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [START: Executing PDO statement for channel]", \PEAR_LOG_DEBUG);

                $result = $db->delete("channels", array("id" => $id));

                if($result != TRUE)
                {
                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [An Exception was thrown by the PDO framwork]", \PEAR_LOG_ERR);

                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [Failed to delete channel]", \PEAR_LOG_ERR);
                }


                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [END: Executing PDO statement for channel]", \PEAR_LOG_DEBUG);
            }

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [END: Looping through ids]", \PEAR_LOG_DEBUG);

            $db = null;
        }
        catch(\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::RemoveChannels [Method Finished]", \PEAR_LOG_DEBUG);
    }

    /**
     * Given a date time, this function returns the next due
     * Channel.
     *
     * @param DateTime $time
     * @return \Swiftriver\Core\ObjectModel\Channel
     */
    public static function SelectNextDueChannel($time)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [Method Invoked]", \PEAR_LOG_DEBUG);

        $channels = array();

        $channel = null;

        if(!isset($time) || $time == null)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [No time supplied, setting time to now]", \PEAR_LOG_DEBUG);

            $time = time();
        }

        try
        {
            $db = self::MongoDatabase();

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [START: Executing statement]", \PEAR_LOG_DEBUG);

            $results = $db->get_where("channels", array("nextRun" => array('$lte' => $time), "active" => true, "inprocess" => false));

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [END: Executing Mongo statment]", \PEAR_LOG_DEBUG);

            if(count($results) > 0)
            {
                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [START: Looping over results]", \PEAR_LOG_DEBUG);

                $row = $results[0];

                $channel = (object)$row;

                $channel->inprocess = true;

                self::SaveChannels(array($channel));

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [END: Looping over results]", \PEAR_LOG_DEBUG);
            }

            $db = null;
        }
        catch(\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SelectNextDueChannel [Method Finished]", \PEAR_LOG_DEBUG);

        return $channel;
    }

    /**
     * Lists all the current Channel in the core
     * @return \Swiftriver\Core\ObjectModel\Channel[]
     */
    public static function ListAllChannels()
    {
        $db = self::MongoDatabase();
        $channels_result = $db->get("channels");

        $channels = array();

        foreach($channels_result as $channel) {
            $channels[] = (object) $channel;
        }

        return $channels;
    }

    /**
     * Given a set of content items, this method will persist
     * them to the data store, if they already exists then this
     * method should update the values in the data store.
     *
     * @param \Swiftriver\Core\ObjectModel\Content[] $content
     */
    public static function SaveContent($content)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $db = self::MongoDatabase();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Method Invoked]", \PEAR_LOG_DEBUG);

        if( !\is_array($content) || \count($content) < 1 )
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [No Content Supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Mrethod Finished]", \PEAR_LOG_DEBUG);

            return;
        }

        try
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [START: Looping through content]", \PEAR_LOG_DEBUG);

            foreach($content as $item)
            {
                $source = $item->source;

                $sourceParams = (array)$source;
                $sourceParams["date_utc"] = \date("Y-m-d h:i:s", $sourceParams["date"]);
                $sourceParams["date_day"] = \date("d", $sourceParams["date"]);
                $sourceParams["date_month"] = \date("m", $sourceParams["date"]);
                $sourceParams["date_year"] = \date("Y", $sourceParams["date"]);
                $sourceParams["date_day_of_year"] = \date("z", $sourceParams["date"]);

                $sourceParams["channelId"] = $source->parent;


                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [START: Saving content source]", \PEAR_LOG_DEBUG);

                // Tally to see if the source id already exists

                $source_result = $db->get_where("sources", array("id" => $source->id));

                $result = null;

                if(count($source_result) > 0) {
                    $db->where("id", $source->id);
                    $result = $db->update("sources", $sourceParams);
                }
                else {
                    $result = $db->insert("sources", $sourceParams);
                }

                if($result != TRUE)
                {
                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [An Exception was thrown by the MongoDB framwork]", \PEAR_LOG_ERR);

                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Could not save the source information]", \PEAR_LOG_ERR);
                }

                $db->where(array());

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [END: Saving content source]", \PEAR_LOG_DEBUG);

                $contentParams = (array)$item;
                $contentParams["date_utc"] = \date("Y-m-d h:i:s", $contentParams["date"]);
                $contentParams["date_day"] = \date("d", $contentParams["date"]);
                $contentParams["date_month"] = \date("m", $contentParams["date"]);
                $contentParams["date_year"] = \date("Y", $contentParams["date"]);
                $contentParams["date_day_of_year"] = \date("z", $contentParams["date"]);

                $contentParams["sourceId"] = $source->id;

                // Tally to see if content item already exists

                $content_result = $db->get_where("content", array("id" => $contentParams->id));

                if(count($content_result) > 0) {
                    $db->where("id", $contentParams->id);
                    $result = $db->update("content", $contentParams);
                }
                else {
                    $result = $db->insert("content", $contentParams);
                }

                $db->where(array());

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [START: Saving content]", \PEAR_LOG_DEBUG);

                if($result !=  TRUE)
                {
                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [An Exception was thrown by the MongoDB framwork]", \PEAR_LOG_ERR);

                    $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Could not save content item]", \PEAR_LOG_ERR);
                }

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [END: Saving content]", \PEAR_LOG_DEBUG);

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [START: Looping through content tags]", \PEAR_LOG_DEBUG);

                if(is_array($item->tags) && count($item->tags) > 0)
                {
                    $db->delete("content_tags", array("id" => $item->id));

                    foreach($item->tags as $tag)
                    {
                        $tagParams = array (
                            "contentId" => $item->id,
                            "tagId" => \md5(\strtolower($tag->text)),
                            "tagType" => $tag->type,
                            "tagText" => \strtolower($tag->text));

                        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [START: Saving Tag]", \PEAR_LOG_DEBUG);

                        $tag_result = $db->get_where("tags", array("id" => $tagParams["tagId"]));

                        if(count($tag_result) < 1) {
                            $result = $db->insert("tags", array("id" => $tagParams["tagId"], "type" => $tag->type, "text" => $tagParams["tagText"]));
                        }
                        else {
                            $result = TRUE;
                        }

                        if($result === FALSE)
                        {
                            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [An Exception was thrown by the MongoDB framwork]", \PEAR_LOG_ERR);
                            
                            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Unable to save the tag]", \PEAR_LOG_ERR);
                        }

                        $content_tags = $db->get_where("content_tags", array("contentId" => $item->id, "tagId" => \md5(\strtolower($tag->text))));

                        if(\count($content_tags) < 1) {
                            $result = $db->insert("content_tags", array("contentId" => $item->id, "tagId" => \md5(\strtolower($tag->text))));

                            if($result === FALSE)
                            {
                                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [An Exception was thrown by the MongoDB framwork]", \PEAR_LOG_ERR);

                                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Unable to save the content tag]", \PEAR_LOG_ERR);
                            }
                        }

                        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [END: Saving Tag]", \PEAR_LOG_DEBUG);
                    }
                }

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [END: Looping through content tags]", \PEAR_LOG_DEBUG);
            }

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [END: Looping through content]", \PEAR_LOG_DEBUG);

            $db = null;
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllChannels [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllChannels [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::SaveContent [Method Finished]", \PEAR_LOG_DEBUG);
    }

    /**
     * Given an array of content is's, this function will
     * fetch the content objects from the data store.
     *
     * @param string[] $ids
     * @return \Swiftriver\Core\ObjectModel\Content[]
     */
    public static function GetContent($ids, $orderby = null)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [Method Invoked]", \PEAR_LOG_DEBUG);

        $db = self::MongoDatabase();

        $content = array();

        if(!\is_array($ids) || \count($ids) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [No Ids supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [Method Finished]", \PEAR_LOG_DEBUG);

            return $content;
        }

        try
        {

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [START: Executing MongoDB statement]", \PEAR_LOG_DEBUG);

            $db->where_in("id", $ids);

            $content_items = $db->get("content");

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [END: Executing PDO statement]", \PEAR_LOG_DEBUG);

            $db->where(array());

            if(count($content_items) > 0)
            {
                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [START: Looping over results]", \PEAR_LOG_DEBUG);

                foreach($content_items as $row)
                {
                    $source = $db->get_where("sources", array("id" => $row["sourceId"]));
                    $contentjson = json_encode(((object)$row));
                    $item = \Swiftriver\Core\ObjectModel\ObjectFactories\ContentFactory::CreateContent($source, $contentjson);
                    $content[] = (object)$item;
                }

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [END: Looping over results]", \PEAR_LOG_DEBUG);
            }

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [START: Getting Content Tags]", \PEAR_LOG_DEBUG);

            foreach($content as $item)
            {
                $tag_result = $db->get_where("content_tags", array("contentId" => $item["id"]));

                if(count($tag_result) > 0) {
                    foreach($tag_result as $tag_row) {
                        $result = $db->get_where("tags", $tag_row["tagId"]);

                        if(count($result) > 0)
                        {
                            $item->tags = array();

                            foreach($result as $row)
                                $item->tags[] = new \Swiftriver\Core\ObjectModel\Tag($row["text"], $row["type"]);
                        }

                        $db = null;
                    }

                }
            }
            
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [START: Getting Content Tags]", \PEAR_LOG_DEBUG);
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContent [Method Finished]", \PEAR_LOG_DEBUG);

        return $content;
    }

    /**
     *
     * @param string[] $parameters
     */
    public static function GetContentList($parameters)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $content_wheres_array = array();

        $num_content_items = 0;
        $num_sources = 0;

        $content_array = array();

        try
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContentList [Method invoked]", \PEAR_LOG_DEBUG);

            $db = self::MongoDatabase();

            $time = (\key_exists("time", $parameters)) ? $parameters["time"] : \time();
            $content_wheres_array["date"] = array('$lte' => $time);

            $state = (key_exists("state", $parameters)) ? $parameters["state"] : null;
            if($state != null)
                $content_wheres_array["state"] = $state;

            $minVeracity = (key_exists("minVeracity", $parameters)) ? $parameters["minVeracity"] : null;
            if($minVeracity != null || $minVeracity === 0) {
                $content_wheres_array["source.score"] = array('$gte' => $minVeracity);
            }

            $maxVeracity = (key_exists("maxVeracity", $parameters)) ? $parameters["maxVeracity"] : null;
            if($maxVeracity != null) {
                if($minVeracity != null || $minVeracity === 0) {
                    $content_wheres_array["source.score"] = array('$lte' => $maxVeracity, '$gte' => $minVeracity);
                }
                else {
                    $content_wheres_array["source.score"] = array('$lte' => $maxVeracity);
                }

                if(($maxVeracity === 0) || ($minVeracity === 0)) {
                    $source_or_wheres_array["score"] = null;
                }
            }

            $type = (key_exists("type", $parameters)) ? $parameters["type"] : null;
            if($type != null)
                $content_wheres_array["source.type"] = $type;

            $subType = (key_exists("subType", $parameters)) ? $parameters["subType"] : null;
            if($subType != null)
                $content_wheres_array["source.subType"] = $subType;

            $source = (key_exists("source", $parameters)) ? $parameters["source"] : null;
            if($source != null)
                $content_wheres_array["source.id"] = $source;

            $tag_result = array();

            $tags = (\key_exists("tags", $parameters)) ? $parameters["tags"] : null;

            if($tags != null) {
                foreach($tags as $tag) {
                    $content_wheres_array["tags.text"] = $tag;
                }
            }

            $pageSize = (key_exists("pageSize", $parameters)) ? $parameters["pageSize"] : null;

            $pageStart = (key_exists("pageStart", $parameters)) ? $parameters["pageStart"] : null;

            $db->limit($pageSize);
            $db->offset($pageStart);
            $db->where($content_wheres_array);
            $content_array = $db->get("content");
            $num_content_items = \count($content_array);

            $types = array(
                "type" => "list",
                "key" => "tags",
                "selected" => $type != null,
                "facets" => $tag_result);
            $navigation["Tags"] = $types;

            if($subType == null)
            {
                $types = array(
                    "type" => "list",
                    "key" => "type",
                    "selected" => $type != null,
                    "facets" => array("name" => $type, "id" => $type, "count" => $num_sources));
                $navigation["Channels"] = $types;
            }

            if($type != null && $source == null)
            {
                $subTypes = array(
                    "type" => "list",
                    "key" => "subType",
                    "selected" => $subType != null,
                    "facets" => array("name" => $subType, "id" => $subType, "count" => $num_sources));
                $navigation["Sub Channels"] = $subTypes;
            }

            if($subType != null && $type != null)
            {
                $sources = array(
                    "type" => "list",
                    "key" => "source",
                    "selected" => $source != null,
                    "facets" => array("name" => $source, "id" => $content_array[0]["source"][0]["name"], "count" => $num_sources));
                $navigation["Sources"] = $sources;
            }
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContentList [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContentList [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetContentList [Method finished]", \PEAR_LOG_DEBUG);

        $content = array();

        foreach($content_array as $content_item) {
            $content[] = (object) $content_item;
        }

        return array (
            "totalCount" => $num_content_items,
            "contentItems" => $content,
            "navigation" => $navigation
        );
    }

    /**
     * Given an array of content items, this method removes them
     * from the data store.
     * @param \Swiftriver\Core\ObjectModel\Content[] $content
     */
    public static function DeleteContent($content)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [Method Invoked]", \PEAR_LOG_DEBUG);

        $db = self::MongoDatabase();

        if (!\is_array($content) || \count($content) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [No content provided]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [Method Finished]", \PEAR_LOG_DEBUG);

            return;
        }

        try
        {
            $ids = array();

            foreach($content as $item) {
                $ids[] = $item->id;
            }

            $db->where_in("id", $ids);
            $result = $db->delete("content");

            if($result != TRUE)
            {
                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [An Exception was thrown by the PDO framwork]", \PEAR_LOG_ERR);

                $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [Could not delete content]", \PEAR_LOG_ERR);
            }

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [END: Looping through content]", \PEAR_LOG_DEBUG);
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [Method Finished]", \PEAR_LOG_DEBUG);

        $db = null;
    }

    /**
     * Given the IDs of Sources, this method
     * gets them from the underlying data store
     *
     * @param string[] $ids
     * @return \Swiftriver\Core\ObjectModel\Source[]
     */
    public static function GetSourcesById($ids)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [Method Invoked]", \PEAR_LOG_DEBUG);

        $sources = array();
        
        $db = self::MongoDatabase();

        if (!\is_array($ids) || \count($ids) < 1)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [No ids supplied]", \PEAR_LOG_DEBUG);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [Method Finished]", \PEAR_LOG_DEBUG);

            return $sources;
        }

        try
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [START: Getting sources]", \PEAR_LOG_DEBUG);

            $db->where_in("id", $ids);
            $sources_result = $db->get("sources");

            foreach($sources_result as $source) {
                $sources[] = (object)$source;
            }


            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [END: Getting sources]", \PEAR_LOG_DEBUG);
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::DeleteContent [$e]", \PEAR_LOG_ERR);
        }

        $db = null;

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::GetSourcesById [Method Finished]", \PEAR_LOG_DEBUG);

        return $sources;
    }

    /**
     * Lists all the current Source in the core
     * @return \Swiftriver\Core\ObjectModel\Source[]
     */
    public static function ListAllSources()
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllSources [Method initiated]", \PEAR_LOG_DEBUG);

        $sources = array();

        $db = self::MongoDatabase();

        try
        {
            $sources_result = $db->get("sources");

            foreach($sources_result as $source) {
                $sources[] = (object)$source;
            }
        }
        catch (\MongoException $e)
        {
            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllSources [An Exception was thrown:]", \PEAR_LOG_ERR);

            $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllSources [$e]", \PEAR_LOG_ERR);
        }

        $db = null;

        $logger->log("Core::Modules::DataContext::Mongo_V1::DataContext::ListAllSources [Method finished]", \PEAR_LOG_DEBUG);

        return $sources;
    }

    /**
     * This method redords the fact that a marker (sweeper) has changed the score
     * of a source by marking a content items as either 'acurate', 'chatter' or
     * 'inacurate'
     *
     * @param string $sourceId
     * @param string $markerId
     * @param string|null $reason
     * @param int $change
     */
    public static function RecordSourceScoreChange($sourceId, $markerId, $change, $reason = null)
    {
        //This function is no loger supported.
        return;
    }
}
?>
