<?php
namespace Swiftriver\Core\ObjectModel;
/**
 * Authentication object
 * @author am[at]swiftly[dot]org
 */
 
class Authentication {
    /**
     * The authentication status
     *
     * @var string
     */
    public $status;

    /**
     * The account performing the authentication
     *
     * @var string
     */
    public $account;

    /**
     * The API Key for the account
     *
     * @var string
     */
    public $api_key;
}
