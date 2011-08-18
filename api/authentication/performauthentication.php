<?php
header('Content-type: application/json');
//Check for the existance of the unique Swift instance Key
if(!isset($_POST["key"])) {
    //If not found then return a JSON error
    echo '{"error":"The request to this service did not contain the required post data \'key\'"}';
    die();
}
if(!isset($_POST["json"])) {
    //If not found then return a JSON error
    echo '{"error":"The request to this service did not contain the required post data \'json\'"}';
    die();
}
//If all pre-checks are ok, attempt to run the API request
else {
    //include the setup file
    include_once(dirname(__FILE__)."/../../Setup.php");

    //Create the base authentication services
    $authentication_base = new Swiftriver\Core\Authentication\AuthenticationServicesBase();

    //Get the JSON response
    $json =  $authentication_base->PerformAuthentication($_POST["key"]);

    //Return the JSON result
    echo $json;
    die();
}
?>