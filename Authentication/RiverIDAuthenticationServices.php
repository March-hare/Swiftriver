<?php
namespace Swiftriver\Core\Authentication;
 
class RiverIDAuthenticationServices extends AuthenticationServicesBase {
    private function ValidateRiverIDLogin($account, $password) {
        // Validate with the RiverID server
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

    public function RemoveAPIKey($api_key, $format) {
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
