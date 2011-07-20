<?php
namespace Swiftriver\AnalyticsProviders;
include_once(\dirname(__FILE__)."/BaseAnalyticsClass.php");
class ContentByChannelOverTimeAnalyticsProvider 
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
        return "ContentByChannelOverTimeAnalyticsProvider";
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

        $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [Method Invoked]", \PEAR_LOG_DEBUG);

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

        $yearDay = (int) \date('z');

        $timeLimit = 5;

        if(\is_array($parameters))
            if(\key_exists("TimeLimit", $parameters))
                $timeLimit = (int) $parameters["TimeLimit"];

        $currentDay = $yearDay;

        $days = "'". ($currentDay + 1) ."',";

        while (($currentDay > 0) && (($yearDay - $currentDay) < $timeLimit))
        {
            $days .= "'$currentDay',";

            $currentDay = $currentDay - 1;
        }

        $days = \rtrim($days, ',');

        $sql =
            "SELECT
                DAYOFYEAR(FROM_UNIXTIME(c.date)) as dayoftheyear,
                count(c.id) as numberofcontentitems,
                ch.id as channelId,
                ch.json as channelJson
            FROM
                SC_Content c JOIN SC_Sources s ON c.sourceId = s.id
                JOIN SC_Channels ch ON s.channelId = ch.id
            WHERE
                DAYOFYEAR(FROM_UNIXTIME(c.date)) in ($days)
            GROUP BY
                channelId, dayoftheyear";

        try
        {
            $db = parent::PDOConnection($request);

            if($db == null)
                return $request;

            $statement = $db->prepare($sql);

            $result = $statement->execute();

            if($result == false)
            {
                $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

                $errorCollection = $statement->errorInfo();

                $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [" . $errorCollection[2] . "]", \PEAR_LOG_ERR);

                return $request;
            }

            $request->Result = array();

            foreach($statement->fetchAll() as $row)
            {
                $channel_name = "";
                $entry_json_decoded = json_decode($row["channelJson"]);

                if(isset($entry_json_decoded->name)) {
                    $channel_name = $entry_json_decoded->name;
                }

                $entry = array(
                    "dayOfTheYear" => $this->DayOfYear2Date($row["dayoftheyear"]),
                    "numberOfContentItems" => $row["numberofcontentitems"],
                    "channelId" => $row["channelId"],
                    "channelName" => $channel_name);

                $request->Result[] = $entry;
            }
        }
        catch(\PDOException $e)
        {
            $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Method finished]", \PEAR_LOG_DEBUG);

        return $request;
    }

    function mongo_analytics($request) {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $parameters = $request->Parameters;

        $yearDay = (int) \date('z');

        $timeLimit = 5;

        if(\is_array($parameters))
            if(\key_exists("TimeLimit", $parameters))
                $timeLimit = (int) $parameters["TimeLimit"];

        $currentDay = $yearDay;

        $days = array();

        $days[] = $currentDay + 1;

        while (($currentDay > 0) && (($yearDay - $currentDay) < $timeLimit))
        {
            $days[] = $currentDay;

            $currentDay = $currentDay - 1;
        }

        $channel_array = array();

        $request->Result = array();

        try
        {
            $db_content = parent::PDOConnection($request);
            $db_sources = parent::PDOConnection($request);
            $db_channels = parent::PDOConnection($request);

            $db_content->where_in("date_day_of_year", $days);
            $content_items = $db_content->get("content");

            $channel_array = array();

            foreach($content_items as $content_item) {
                $source_id = $content_item["source"]["id"];
                $source_items = $db_sources->get_where("sources", array("id" => $source_id));

                foreach($source_items as $source_item) {
                    $channel_id = $source_item["channelId"];
                    if(!\in_array($channel_id, $channel_array)) {
                        $channel_array[$channel_id] = array();
                    }

                    $channels = $db_channels->get_where("channels", array("id" => $channel_id));

                    foreach($channels as $channel) {
                        if(!\in_array($content_item->date_day_of_year, $channel_array[$channel_id])) {
                            $channel_array[$channel_id][$content_item->date_day_of_year] = array();
                        }
                        
                        $channel_array[$channel_id][$content_item["date_day_of_year"]]["channelId"] = $channel_id;
                        $channel_array[$channel_id][$content_item["date_day_of_year"]]["channelName"] = $channel["name"];

                        if(!\in_array($channel_id, $channel_array[$channel_id][$content_item["date_day_of_year"]]["numberOfContentItems"])) {
                            $channel_array[$channel_id][$content_item["date_day_of_year"]]["numberOfContentItems"] = 1;
                        }
                        else {
                            $channel_array[$channel_id][$content_item["date_day_of_year"]]["numberOfContentItems"] += 1;
                        }

                        $channel_array[$channel_id][$content_item["date_day_of_year"]]["dayOfTheYear"] = $content_item["date_day_of_year"];
                    }
                }
            }
        }
        catch(\MongoException $e) {
            $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        foreach($channel_array as $channel_array_item) {
            foreach($channel_array_item as $channel_array_item_day) {
                $request->Result[] = $channel_array_item_day;
            }
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

    private  function DayOfYear2Date( $dayofyear, $format = 'd-m-Y' )
    {
        $day = intval( $dayofyear );
        $day = ( $day == 0 ) ? $day : $day - 1;
        $offset = intval( intval( $dayofyear ) * 86400 );
        $str = date( $format, strtotime( 'Jan 1, ' . date( 'Y' ) ) + $offset );
        return( $str );
    }
}
?>
