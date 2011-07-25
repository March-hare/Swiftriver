<?php
namespace Swiftriver\AnalyticsProviders;
include_once(\dirname(__FILE__)."/BaseAnalyticsClass.php");
class SourcesByChannelOverTimeAnalyticsProvider
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
        return "SourcesByChannelOverTimeAnalyticsProvider";
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

        $days = "";

        while (($currentDay > 0) && (($yearDay - $currentDay) < $timeLimit))
        {
            $days .= "'$currentDay',";

            $currentDay = $currentDay - 1;
        }

        $days = \rtrim($days, ',');

        $sql =
            "SELECT
                DAYOFYEAR(FROM_UNIXTIME(s.date)) as dayoftheyear,
                count(s.id) as numberofsources,
                ch.id as channelId,
                ch.json as channelJson,
                s.json as sourceJson
            FROM
                SC_Sources s JOIN SC_Channels ch ON s.channelId = ch.id
            WHERE
                DAYOFYEAR(FROM_UNIXTIME(s.date)) in ($days)
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
                $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

                $errorCollection = $statement->errorInfo();

                $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [" . $errorCollection[2] . "]", \PEAR_LOG_ERR);

                return $request;
            }

            $request->Result = array();

            foreach($statement->fetchAll() as $row)
            {
                $channel_name = "";
                $source_name = "";
                $entry_channel_json_decoded = json_decode($row["channelJson"]);
                $entry_source_json_decoded = json_decode($row["sourceJson"]);

                if(isset($entry_channel_json_decoded->name)) {
                    $channel_name = $entry_channel_json_decoded->name;
                }

                if(isset($entry_source_json_decoded->name)) {
                    $source_name = $entry_channel_json_decoded->name;
                }

                $entry = array(
                    "dayOfTheYear" => $this->DayOfYear2Date($row["dayoftheyear"]),
                    "numberOfSources" => $row["numberofsources"],
                    "channelId" => $row["channelId"],
                    "channelName" => $channel_name,
                    "sourceName" => $source_name);

                $request->Result[] = $entry;
            }
        }
        catch(\PDOException $e)
        {
            $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Method finished]", \PEAR_LOG_DEBUG);

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

        $source_array = array();

        $request->Result = null;

        try
        {
            $db_sources = parent::PDOConnection($request);
            $db_channels = parent::PDOConnection($request);

            $db_sources->where_in("date_day_of_year", $days);
            $source_items = $db_sources->get("sources");

            foreach($source_items as $source_item) {
                $channel_id = $source_item["channelId"];
                if(!\in_array($channel_id, $source_array)) {
                    $source_array[$channel_id] = array();
                }

                $channels = $db_channels->get_where("channels", array("id" => $channel_id));

                foreach($channels as $channel) {
                    if(!\in_array($source_item["date_day_of_year"], $source_array[$channel_id])) {
                        $source_array[$channel_id][$source_item["date_day_of_year"]] = array();
                    }

                    $source_array[$channel_id][$source_item["date_day_of_year"]]["channelId"] = $channel_id;
                    $source_array[$channel_id][$source_item["date_day_of_year"]]["channelName"] = $channel["name"];

                    if(!\in_array($channel_id, $source_array[$channel_id][$source_item["date_day_of_year"]]["numberOfContentItems"])) {
                        $source_array[$channel_id][$source_item["date_day_of_year"]]["numberOfContentItems"] = 1;
                    }
                    else {
                        $source_array[$channel_id][$source_item["date_day_of_year"]]["numberOfContentItems"] += 1;
                    }

                    $source_array[$channel_id][$source_item["date_day_of_year"]]["dayOfTheYear"] = $source_item["date_day_of_year"];
                }
            }
        }
        catch(\MongoException $e) {
            $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        foreach($source_array as $source_array_item) {
            foreach($source_array_item as $source_array_item_day) {
                if($request->Result == null) {
                    $request->Result = array();
                }

                $request->Result[] = $source_array_item_day;
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
