<?php
namespace Swiftriver\Core\Modules\DataContext\Mongo_V1;
class DataContextConfigurationHandler extends \Swiftriver\Core\Configuration\ConfigurationHandlers\BaseConfigurationHandler {

    private $configurationFilePath;

    /**
     * @var string
     */
    public $Host;

    /**
     * @var string
     */
    public $Port;

    /**
     * @var string
     */
    public $User;

    /**
     * @var string
     */
    public $Password;

    /**
     * @var string
     */
    public $Database;

    /**
     * @var string
     */
    public $Persist;

    /**
     * @var string
     */
    public $ParsistKey;

    /**
     * @var simpleXMLElement
     */
    public $xml;

    public function __construct($configurationFilePath) {
        $this->configurationFilePath = $configurationFilePath;
        $xml = simplexml_load_file($configurationFilePath);
        $this->xml = $xml;
        foreach($xml->properties->property as $property) {
            switch((string) $property["name"]) {
                case "Host" :
                    $this->Host = $property["value"];
                    break;
                case "Port" :
                    $this->Port= $property["value"];
                    break;
                case "User" :
                    $this->User = $property["value"];
                    break;
                case "Password" :
                    $this->Password = $property["value"];
                    break;
                case "Database" :
                    $this->Database = $property["value"];
                    break;
                case "Persist" :
                    $this->Persist = $property["value"];
                    break;
                case "PersistKey" :
                    $this->PersistKey = $property["value"];
                    break;
            }
        }
    }

    public function Save() {
        $this->xml->asXML($this->configurationFilePath);
    }
}
?>
