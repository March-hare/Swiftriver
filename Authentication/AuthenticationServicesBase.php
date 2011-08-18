<?php
namespace Swiftriver\Core\Authentication;

class AuthenticationServicesBase {
    public function ParseAuthenticationToJSON($authentication_entry)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Authentication::AuthenticationServicesBase::ParseAuthenticationToJSON [Method invoked]", \PEAR_LOG_INFO);

        $json = null;
        $json->authentication->apikey = $authentication_entry->api_key;
        $json->authentication->status = $authentication_entry->status;
        $json->authentication->account = $authentication_entry->account;

        $logger->log("Core::Authentication::AuthenticationServicesBase::ParseAuthenticationToJSON [Method invoked]", \PEAR_LOG_INFO);

        return json_encode($json);
    }

    public function ParseAPIKeyCreationToJSON($authentication_entry)
    {
        $logger = \Swiftriver\Core\Setup::GetLogger();
        $logger->log("Core::Authentication::AuthenticationServicesBase::ParseAPIKeyCreationToJSON [Method invoked]", \PEAR_LOG_INFO);

        $json = null;
        $json->authentication->status = $authentication_entry->status;
        $json->authentication->account = $authentication_entry->account;
        $json->authentication->apikey = $authentication_entry->api_key;

        $logger->log("Core::Authentication::AuthenticationServicesBase::ParseAPIKeyCreationToJSON [Method invoked]", \PEAR_LOG_INFO);

        return json_encode($json);
    }

    public function PerformAuthentication($key)
    {
        return $this->GetAuthenticationService()->AuthenticateAPIKey($key);
    }

    public function AddAPIKey($account, $password)
    {
        return $this->GetAuthenticationService()->CreateNewAPIKey($account, $password);
    }

    public function RemoveAPIKey($key)
    {
        return $this->GetAuthenticationService()->RemoveAPIKey($key);
    }

    protected function FormatErrorMessage($error)
    {
        return '{"message":"'.str_replace('"', '\'', $error).'"}';
    }

    private function GetAuthenticationService()
    {
        $authentication_service = \Swiftriver\Core\Setup::AuthenticationConfiguration()->HandlerName;

        if($authentication_service == "RiverID") {
            $authentication_service = new \Swiftriver\Core\Authentication\RiverIDAuthenticationServices();
        }

        return $authentication_service;
    }
}
