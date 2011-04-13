<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Swiftriver\Core\Modules\SiSPS\PushParsers;
interface IPushParser{
    /**
     * Provided with the raw content, this method parses the raw content
     * and converts it to SwiftRiver content object model
     *
     * @param String $raw_content
     * @return Swiftriver\Core\ObjectModel\Content[] contentItems
     */
    public function PushAndParse($raw_content);

    /**
     * This method returns a string describing the type of sources
     * it can parse. For example, the RSSParser returns "Feeds".
     *
     * @return string type of sources parsed
     */
    public function ReturnType();
}
?>