<?php
namespace Swiftriver\AnalyticsProviders;
include_once(\dirname(__FILE__)."/BaseAnalyticsClass.php");
class AccumulatedContentOverTimeAnalyticsProvider
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
        return "AccumulatedContentOverTimeAnalyticsProvider";
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

    private function mysql_analytics($request) {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $sql =
            "SELECT
                c.date as date,
                count(c.id) as numberofcontentitems
            FROM
                SC_Content c
            GROUP BY
                DAYOFYEAR(FROM_UNIXTIME(c.date))
            ORDER BY
                c.date ASC";

        try
        {
            $db = parent::PDOConnection($request);

            if($db == null)
                return $request;

            $statement = $db->prepare($sql);

            $result = $statement->execute();

            if($result == false)
            {
                $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

                $errorCollection = $statement->errorInfo();

                $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [" . $errorCollection[2] . "]", \PEAR_LOG_ERR);

                return $request;
            }

            $request->Result = array();

            $accumulatedTotal = 0;
            $lastdate = null;

            foreach($statement->fetchAll() as $row)
            {
                $date = date("d-m-Y", $row['date']);

                if($lastdate != null)
                {
                    while($date != date("d-m-Y", \strtotime("$lastdate +1 day")) && $lastdate < date("d-m-Y"))
                    {
                        $entry = array
                        (
                            "date" => $lastdate,
                            "accumulatedtotal" => $accumulatedTotal,
                        );

                        $request->Result[] = $entry;

                        $lastdate = date('d-m-Y', \strtotime("$lastdate +1 day"));
                    }
                }

                $accumulatedTotal += $row["numberofcontentitems"];

                $entry = array
                (
                    "date" => $date,
                    "accumulatedtotal" => $accumulatedTotal,
                );

                $request->Result[] = $entry;

                $lastdate = $date;


            }
        }
        catch(\PDOException $e)
        {
            $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
        }

        $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [Method finished]", \PEAR_LOG_DEBUG);

        return $request;
    }

    private function mongo_analytics($request) {
        $logger = \Swiftriver\Core\Setup::GetLogger();

        $request->Result = null;
        
        try {
            $db = parent::PDOConnection($request);
            $content = $db->get("content");

            $content_date = null;

            $content_statistics = array();

            $array_index = -1;
            $accumulated_total = 0;
            $date = null;

            foreach($content as $content_item) {
                $timestamp = $content_item["date"];
                
                $content_item_day_of_year = \date('z', $timestamp);

                if($content_date != $content_item_day_of_year) {
                    $content_date = $content_item_day_of_year;

                    if($array_index > -1) {
                        // Add statistics
                        $content_statistics[]  = array
                        (
                            "date" => $date,
                            "accumulatedtotal" => $accumulated_total,
                        );
                    }

                    $accumulated_total = 0;

                    $array_index ++;
                }
                else {
                    $accumulated_total ++;
                }

                // Get the date for the new entry
                $date = \date("d-m-Y", $timestamp);
            }

            if($request->Result == null) {
                $request->Result = array();
            }

            $request->Result[] = $content_statistics;
        }
        catch(\MongoException $e) {
            $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [An exception was thrown]", \PEAR_LOG_ERR);

            $logger->log("Swiftriver::AnalyticsProviders::AccumulatedContentOverTimeAnalyticsProvider::ProvideAnalytics [$e]", \PEAR_LOG_ERR);
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
