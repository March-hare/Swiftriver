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

        $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Method Invoked]", \PEAR_LOG_DEBUG);

        $parameters = $request->Parameters;

        $yearDay = (int) \date('z');

        $timeLimit = 5;
        $timeFrom = 0;
        $timeTo = \time();

        $queryType = "TimeLimit";

        if(\is_array($parameters)) {
            if(\key_exists("TimeLimit", $parameters)) {
                $queryType= "TimeLimit";

                $timeLimit = (int) $parameters["TimeLimit"];
                $timeFrom = $timeLimit;
                if(\key_exists("TimeTo", $parameters)) {
                    $timeTo = (int) $parameters["TimeTo"];
                }
            }
            else if(\key_exists("TimeRange", $parameters)) {
                $queryType= "TimeRange";

                $timeFrom = (int) $parameters["TimeFrom"];
                if(\key_exists("TimeTo", $parameters)) {
                    $timeTo = (int) $parameters["TimeTo"];
                }
            }
        }

        $currentDay = $yearDay;

        $days = "";

        $sql = "";

        if($queryType == "TimeLimit") {
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
        }
        else if($queryType == "TimeRange") {
            $sql =
                "SELECT
                    FROM_UNIXTIME(c.date) as `datetime`,
                    count(s.id) as numberofsources,
                    ch.id as channelId,
                    ch.json as channelJson,
                    s.json as sourceJson
                FROM
                    SC_Sources s JOIN SC_Channels ch ON s.channelId = ch.id
                WHERE
                    c.date between $timeFrom and $timeTo
                GROUP BY
                    channelId, dayoftheyear";
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
            $logger->log("Swiftriver::AnalyticsProviders::SourcesByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Executing SQL: $sql]", \PEAR_LOG_ERR);
            
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

                $entry = null;

                if($queryType == "TimeLimit") {
                    $entry = array(
                        "dayOfTheYear" => $this->DayOfYear2Date($row["dayoftheyear"]),
                        "numberOfSources" => $row["numberofsources"],
                        "channelId" => $row["channelId"],
                        "channelName" => $channel_name,
                        "sourceName" => $source_name);
                }
                else if($queryType == "TimeRange") {
                    $entry = array(
                        "dateTime" => $row["datetime"],
                        "numberOfSources" => $row["numberofsources"],
                        "channelId" => $row["channelId"],
                        "channelName" => $channel_name,
                        "sourceName" => $source_name);
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
