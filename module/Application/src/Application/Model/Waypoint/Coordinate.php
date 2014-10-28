<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Waypoint;

class Coordinate
{
    /**
     * @var float Coordinate\Latitude
     */
    public $lat;
    /**
     * @var float Coordinate\Longitude
     */
    public $lon;

    public function __construct($latitude=null,$longitude=null) {
        if(isset($latitude) && isset($longitude)) {
            $this->lat = new Coordinate\Latitude($latitude);
            $this->lon = new Coordinate\Longitude($longitude);
        }
    }

    public static function _cleanCoord($str) {
        $str = str_replace(",",".",(string) $str);
        $str = preg_replace('/^\s*[\+]?([^\s]+)\s*$/', '$1', $str);
        return $str;
    }

    public static function _validCoord($str) {
        $valid = (boolean) preg_match("/^\s*[\-\+]?[0-9]{1,3}\.[0-9]+\s*$/", (string) $str);
        return $valid;
    }
}
