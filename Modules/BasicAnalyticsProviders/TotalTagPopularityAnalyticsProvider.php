<?php
namespace Swiftriver\AnalyticsProviders;
include_once(\dirname(__FILE__)."/BaseAnalyticsClass.php");
class TotalTagPopularityAnalyticsProvider
    extends BaseAnalyticsClass
    implements \Swiftriver\Core\Analytics\IAnalyticsProvider
{
    /**
     * Function that should return the name of the
     * given AnalyticsProvider.
     *
     * @return string
     */
    public function ProviderType()
    {
        return "TotalTagPopularityAnalyticsProvider";
    }

    /**
     * Function that when implemented by a derived
     * class should return an object that can be
     * json encoded and returned to the UI to
     * provide analytical information about the
     * underlying data.
     *
     * @param \Swiftriver\Core\Analytics\AnalyticsRequest $parameters
     * @return \Swiftriver\Core\Analytics\AnalyticsRequest
     */
    public function ProvideAnalytics($request)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [Method Invoked]", \PEAR_LOG_DEBUG);

        switch ($request->DataContextType)
        {
            case "\Swiftriver\Core\Modules\DataContext\MySql_V2\DataContext":
                return $this->mysql_analytics($request);
            break;
            case "\Swiftriver\Core\Modules\DataContext\Mongo_V1\DataContext":
                return $this->mongo_analytics($request);
            break;
            default :
                return null;
        }
    }

    function mysql_analytics($request) {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $parameters = $request->Parameters;

        $limit = 20;

        if(\is_array($parameters))
            if(\key_exists("Limit", $parameters))
                $limit = (int) $parameters["Limit"];


        $sql =
            "select
                t.text as 'tag',
                count(*) as 'popularity'
            from
                SC_Tags t join SC_Content_Tags ct on t.id = ct.tagId
            group by
                t.text
            order by
                count(*) DESC
            limit $limit";

        try
        {
            $db = parent::PDOConnection($request);

            if($db == null)
                return $request;

            $statement = $db->prepare($sql);

            $result = $statement->execute();

            if($result == false)
            {
                $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

                $errorCollection = $statement->errorInfo();

                $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [" . $errorCollection[2] . "]", \PEAR_LOG_ERR);

                return $request;
            }

            $request->Result = array();

            foreach($statement->fetchAll() as $row)
            {
                $entry = array
                    (
                        "tag" => $row["tag"],
                        "count" => $row["popularity"]
                    );

                $request->Result[] = $entry;
            }
        }
        catch(\PDOException $e)
        {
            $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [Method finished]", \PEAR_LOG_DEBUG);

        return $request;
    }

    function mongo_array_sort($a, $subkey) {
        foreach($a as $k=>$v) {
            $b[$k] = strtolower($v[$subkey]);
        }

        asort($b);

        foreach($b as $key=>$val) {
            $c[] = $a[$key];
        }
            
        return $c;
    }

    function mongo_analytics($request) {
        $logger = \Swiftriver\Core\Setup::GetLogger();
        
        $request->Result = null;
        $tag_array = array();

        $limit = 20;

        $parameters = $request->Parameters;

        if(\is_array($parameters))
            if(\key_exists("Limit", $parameters))
                $limit = (int) $parameters["Limit"];

        $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [Set limit $limit]", \PEAR_LOG_INFO);

        try
        {
            $db = parent::PDOConnection($request);
            $content_tags = $db->get("content_tags");

            foreach($content_tags as $content_tag) {
                $tags = $db->get_where('tags', array('id' => $content_tag["tagId"]));
                foreach($tags as $tag) {
                    if(!\in_array($tag["text"], $tag_array)) {
                        $tag_array[$tag["text"]] = array("tag" => $tag["text"], "count" => 1);
                    }
                    else {
                        $tag_array[$tag["text"]]["count"] += 1;
                    }
                }
            }
        }
        catch(\MongoException $e) {
            $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::TotalTagPopularityAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        $sorted_tag_array = $this->mongo_array_sort($tag_array, "count");

        $num_items = 1;

        foreach($sorted_tag_array as $tag_item) {
            if($num_items > $limit)
                continue;
            
            if($request->Result == null) {
                $request->Result = array();
            }
            
            $request->Result[] = $tag_item;

            $num_items ++;
        }

        return $request;
    }

    /**
     * Function that returns an array containing the
     * fully qualified types of the data content's
     * that the deriving Analytics Provider can work
     * against
     *
     * @return string[]
     */
    public function DataContentSet()
    {
        return array("\\Swiftriver\\Core\\Modules\\DataContext\\MySql_V2\\DataContext");
    }
}
?>
