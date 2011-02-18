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

        $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Method Invoked]", \PEAR_LOG_DEBUG);

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
        }
        else if($queryType == "TimeRange") {
            $sql =
                "SELECT
                    FROM_UNIXTIME(c.date) as `datetime`,
                    count(c.id) as numberofcontentitems,
                    ch.id as channelId,
                    ch.json as channelJson
                FROM
                    SC_Content c JOIN SC_Sources s ON c.sourceId = s.id
                    JOIN SC_Channels ch ON s.channelId = ch.id
                WHERE
                    c.date between $timeFrom and $timeTo
                GROUP BY
                    channelId, dayoftheyear";
        }
        
        try
        {
            $logger->log("Swiftriver::AnalyticsProviders::ContentByChannelOverTimeAnalyticsProvider::ProvideAnalytics [Executing SQL: $sql]", \PEAR_LOG_ERR);

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
                $entry = null;

                if($queryType == "TimeLimit") {
                    $entry = array(
                        "dayOfTheYear" => $this->DayOfYear2Date($row["dayoftheyear"]),
                        "numberOfContentItems" => $row["numberofcontentitems"],
                        "channelId" => $row["channelId"],
                        "channelName" => $channel_name);
                }
                else if($queryType == "TimeRange") {
                    $entry = array(
                        "dateTime" => $row["datetime"],
                        "numberOfContentItems" => $row["numberofcontentitems"],
                        "channelId" => $row["channelId"],
                        "channelName" => $channel_name);
                }
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
