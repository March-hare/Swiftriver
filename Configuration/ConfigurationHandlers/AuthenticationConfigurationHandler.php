<?php
namespace Swiftriver\Core\Configuration\ConfigurationHandlers;
/**
 * Configuration access to the switchable IDataContext type
 * @author mg[at]swiftly[dot]org
 */
class AuthenticationConfigurationHandler extends BaseConfigurationHandler
{
    /**
     * The name of the Authentication Handler
     * @var Type
     */
    public $HandlerName;

    /**
     * The address of the Authentication Service
     * @var Type
     */
    public $HandlerAddress;

    /**
     * Constructor for the DALConfigurationHandler
     * @param string $configurationFilePath
     */
    public function __construct($configurationFilePath) 
    {
        //Use the base class to read in the configuration
        $xml = parent::SaveOpenConfigurationFile($configurationFilePath, "properties");

        //loop through the configuration properties
        foreach($xml->properties->property as $property) 
        {
            //Switch on the property name
            switch((string) $property["name"])
            {
                case "HandlerName" :
                    $this->HandlerName = $property["value"];
                    break;
                case "ServerAddress" :
                    $this->HandlerAddress = $property["value"];
                    break;
            }
        }
    }
}
?>
