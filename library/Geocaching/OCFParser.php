<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 10/27/11
 * Time: 1:16 PM
 * To change this template use File | Settings | File Templates.
 */
 
class Geocaching_OCFParser
{
    public static $lastError;

    public static function parseOCF($xmlFile)
    {

        $ocfData = null;
        if(version_compare(phpversion(), "5", ">=")) {

        }
    }
    private static function _PHP5_parse($xmlFile)
    {
        if(!function_exists("simplexml_load_file")) {
            throw new Exception(__METHOD__." 'simplexml_load_file' function does not exist");
        }

        $xml = simplexml_load_file($xmlFile);
        if(!($xml instanceof SimpleXMLElement)) {
            throw new Exception(__METHOD__." simplexml was not able to parse '$xmlFile'");
        }


    }
}