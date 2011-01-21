<?php
namespace Swiftriver\Core;
require_once 'PHPUnit/Framework.php';
class RunAnalyticsTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        include_once(\dirname(__FILE__)."/../../../Setup.php");
    }

    public function test()
    {
        /*
        $workflow = new Workflows\Analytics\RunAnalyticsQuery();

        $key = "swiftriver_dev";

        $json = '{"RequestType":"ContentByChannelOverTimeAnalyticsProvider","Parameters":{"TimeLimit":7}}';

        $result = $workflow->RunQuery($json, $key);
        */

        //TODO: set this to the right file and ensure that chown to www-data
        $messages_destination = "http://173.203.80.47/core/api/contentservices/getcontent.php";
        $messages_postdata = 'key=projectreddev&json={state}';
        $messages_json = "global/js/messages.json";

        //TODO: set this to the right file and ensure that chown to www-data
        $counts_destination = "http://173.203.80.47/core/api/contentservices/getcontent.php";
        $counts_postdata = 'key=projectreddev&json={state}';
        $counts_json = "data/counts.json";

        // Set up the cURL to pull the latest messages from swift
        $c = curl_init($messages_destination);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $messages_postdata);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        if(! $response = curl_exec($c)){
            $msg = $_SERVER["SCRIPT_NAME"] . ": could not reach Swift API for new msg pull";
            error_log($msg);
            return;
        }
        curl_close($c);

        $response_json = json_decode($response);

    }
}
?>
