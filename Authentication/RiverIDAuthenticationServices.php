<?php
namespace Swiftriver\Core\Authentication;
 
class RiverIDAuthenticationServices extends AuthenticationServicesBase {
    private function ValidateRiverIDLogin($account, $password) {
        // Validate with the RiverID server
        $handler_address = \Swiftriver\Core\Setup::AuthenticationConfiguration()->HandlerAddress;
        $handler_address = rtrim($handler_address, "/")."/signin";

        $fields = array('email' => $account, 'password' => $password);

        $fields_string = "";

        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }

        rtrim($fields_string,'&');

        // Open connection to RiverID
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $handler_address);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        // Execute an HTTP POST
        $result_json = json_decode(curl_exec($ch));

        // Close the connextion
        curl_close($ch);

        return $result_json->status;
    }
    
    public function AuthenticateAPIKey($api_key, $format = 'json') {
        //Setup the logger
        
        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Authentication::AuthenticationServicesBase::AuthenticateAPIKey [Method invoked]", \PEAR_LOG_INFO);

        try
        {
            // Authenticate the API Key via the DAL
            $repository = new \Swiftriver\Core\DAL\Repositories\AuthenticationRepository();
            $result = $repository->IsRegisterdCoreAPIKey($api_key);

            if($format == 'json') {
                return parent::ParseAuthenticationToJSON($result);
            }
            else if($format == 'object') {
                return $result;
            }
        }
        catch (\Exception $e)
        {
            //get the exception message
            $message = $e->getMessage();
            
            $logger->log("Core::Authentication::AuthenticationServicesBase::AuthenticateAPIKey [An exception was thrown]", \PEAR_LOG_DEBUG);
            $logger->log("Core::Authentication::AuthenticationServicesBase::AuthenticateAPIKey [$message]", \PEAR_LOG_ERR);
            $logger->log("Core::Authentication::AuthenticationServicesBase::AuthenticateAPIKey [Method finished]", \PEAR_LOG_INFO);

            return parent::FormatErrorMessage("An exception was thrown: $message");
        }
    }

    public function CreateNewAPIKey($account, $password) {
        // Connect with RiverID and establish the status

        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Authentication::AuthenticationServicesBase::CreateNewAPIKey [Method invoked]", \PEAR_LOG_INFO);

        try
        {
            // Authenticate the API Key via the DAL
            $repository = new \Swiftriver\Core\DAL\Repositories\AuthenticationRepository();

            $api_key = null;

            if($this->ValidateRiverIDLogin($account, $password)) {
                // Create an API Key
                $api_key = \md5(\time());
            }

            $result = null;
            $result->apikey = $api_key;
            $result->account = $account;

            if(!is_null($api_key)) {
                $repository->AddRegisteredCoreAPIKey($account, $api_key);
                $result->status = "success";
            }
            else {
                $result->status = "fail";
            }

            return parent::ParseAPIKeyCreationToJSON($result);

        }
        catch (\Exception $e)
        {
            //get the exception message
            $message = $e->getMessage();

            $logger->log("Core::Authentication::AuthenticationServicesBase::CreateNewAPIKey [An exception was thrown]", \PEAR_LOG_DEBUG);
            $logger->log("Core::Authentication::AuthenticationServicesBase::CreateNewAPIKey [$message]", \PEAR_LOG_ERR);
            $logger->log("Core::Authentication::AuthenticationServicesBase::CreateNewAPIKey [Method finished]", \PEAR_LOG_INFO);

            return parent::FormatErrorMessage("An exception was thrown: $message");
        }
    }

    public function RemoveAPIKey($api_key) {
        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Authentication::AuthenticationServicesBase::RemoveAPIKey [Method invoked]", \PEAR_LOG_INFO);

        try
        {
            // Authenticate the API Key via the DAL
            $repository = new \Swiftriver\Core\DAL\Repositories\AuthenticationRepository();
            $repository->RemoveRegisteredCoreAPIKey($api_key);

            $status = null;
            $status->status = "success";

            return json_encode($status);
        }
        catch (\Exception $e)
        {
            //get the exception message
            $message = $e->getMessage();

            $logger->log("Core::Authentication::AuthenticationServicesBase::RemoveAPIKey [An exception was thrown]", \PEAR_LOG_DEBUG);
            $logger->log("Core::Authentication::AuthenticationServicesBase::RemoveAPIKey [$message]", \PEAR_LOG_ERR);
            $logger->log("Core::Authentication::AuthenticationServicesBase::RemoveAPIKey [Method finished]", \PEAR_LOG_INFO);

            return parent::FormatErrorMessage("An exception was thrown: $message");
        }
    }
}
