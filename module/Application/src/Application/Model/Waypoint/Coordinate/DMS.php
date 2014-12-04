<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Waypoint\Coordinate;

class DMS
{
    /**
     * @var integer $degrees
     */
    public $degrees;
    /**
     * @var integer $minutes
     */
    public $minutes;
    /**
     * @var float $seconds
     */
    public $seconds;
    /**
     * @var string $compass   N S E W
     */
    public $compass;

    /**
     * @param Latitude|Longitude|integer $d
     * @param null|integer $m
     * @param null|float $s
     * @param null|string
     * @throws EXCEPTION if invalid parameters specified
     * @see setFromLL()
     */
    public function __construct($d=null,$m=null,$s=null,$ch=null) {
        if(isset($d)) {
            if(($d instanceof Latitude) || ($d instanceof Longitude)) {
                $this->setFromLL($d);
            } elseif(isset($ch)) {
                $this->degrees = intval($d);
                $this->minutes = intval($m);
                $this->seconds = floatval($s);
                $this->compass = "$ch";
            } elseif(!(isset($s))) {
                if(!(isset($m))) {

                }
            } else {
                throw new \Exception(__METHOD__." if degrees is specified, compass heading is also required to init!");
            }
        }
    }

    /**
     * set values from an instance of a latitude or longitude
     * @param Latitude|Longitude $ll
     * @throws Exception if param is not an instance of Latitude or Longitude
     */
    public function setFromLL($ll) {
        if($ll instanceof Latitude) {
            $this->compass = ($ll->val < 0) ? 'S' : 'N';
        } elseif($ll instanceof Longitude) {
            $this->compass = ($ll->val < 0) ? 'W' : 'E';
        } else {
            throw new \Exception(__METHOD__." requires input of a Application\Model\Waypoint\Coordinate\Latitude or Application\Model\Waypoint\Coordinate\Longitude instance!");
        }

        $dmsArr = self::convDectoDMSArray($ll->val);
        $this->degrees = $dmsArr['degrees'];
        $this->minutes = $dmsArr['minutes'];
        $this->seconds = $dmsArr['seconds'];
    }

    public static function convDectoDMSArray($d) {
        $dms = array();
        $d = abs($d);
        $dms['degrees'] = intval($d);
        $_md = ($d - $dms['degrees']) * 60;
        $dms['minutes'] = intval($_md);
        $dms['seconds'] = floatval(sprintf("%.4f",($_md - $dms['minutes']) * 60));
        return $dms;
    }

    /**
     * return the values of this instance as a formatted lat/lon string
     * @return string
     * @see self::getDMSAsString()
     */
    public function getAsString() {
        return self::getDMSAsString($this);
    }

    /**
     * return the values of an instance of DMS as a formatted lat/lon string
     * @static
     * @param DMS|integer $deg
     * @param null|integer $min
     * @param null|float $sec
     * @param null|string $ch
     * @return string
     * @throws Exception if $deg (and $ch if $deg is not a populated instance of DMS) are not set
     */
    public static function getDMSAsString($deg,$min=null,$sec=null,$ch=null) {
        $d = null;
        $m = intval($min);
        $s = floatval($sec);
        $c = "$ch";

        if($deg instanceof DMS) {
            $d = $deg->degrees;
            $m = $deg->minutes;
            $s = $deg->seconds;
            $c = $deg->compass;
        } else {
            $d = intval($deg);
        }

        if(is_null($deg) || ($c === '')) {
            throw new \Exception(__METHOD__.' a minimum of degrees and compass must be set');
        }
        if(!preg_match("/$c/","NSEW")) {
            throw new \Exception(__METHOD__.' compass must be one of: N, S, E, W!');
        }

        return sprintf("%d° %d' %.4f\" %s", $d, intval($m), floatval($s), $c);
    }

    /**
     * return the decimal equivalent of the values of this instance (assumes properly populated)
     * @return float
     * @see self::getDMSAsDec()
     */
    public function getAsDec() {
        return self::getDMSAsDec($this);
    }

    /**
     * return the decimal equivalent of a DMS value. Assumes proper values
     * @static
     * @param DMS|integer $deg
     * @param null|integer $min
     * @param null|float $sec
     * @param null|string $ch
     * @return float
     */
    public static function getDMSAsDec($deg=null,$min=null,$sec=null,$ch=null) {
        $c = "$ch";

        if($deg instanceof DMS) {
            $min = $deg->minutes;
            $sec = $deg->seconds;
            $ch  = $deg->compass;
            $deg = $deg->degrees;
        }

        $mult = (($ch === 'S') || ($ch === 'W')) ? -1.0 : 1.0;
        return $mult * self::convDMStoDec($deg,$min,$sec);
    }

    public static function convDMStoDec($d,$m=null,$s=null) {
        $dp = abs(floatval($d));
        $m = abs(floatval($m));
        $s = abs(floatval($s));
        $mult = ($d / $dp);
        $dec = (float) $mult * ($dp + ($m/60) + ($s/3600));
        return $dec;
    }

    public function getAsRad() {
        return self::convDegtoRad($this->getAsDec());
    }

    /**
     * Convert degrees to radians
     * @param float $deg
     * @return float   radian value
     */
    public static function convDegtoRad ($deg)
    {
        $rad = $deg * M_PI / 180.0;
        return $rad;
    }

    /**
     * Convert radians to degress
     * @param float $rad
     * @return float   degree value
     */
    public static function convRadtoDeg ($rad)
    {
        $deg = $rad * 180 / M_PI;
        return $deg;
    }

    public static function parseTextCoord($c) {
        $d = null;
        // assume positive
        $mult = 1;

        if(is_numeric($c)) {
            $d = floatval($c);
        } elseif(is_string($c)) {

            // north matches positive so remove 'N' and any surrounding space
            $cc = preg_replace('/\s*[N|E]\s*/','',$c);
            // if includes 'S', set multiplier to negative and remove the S and any surrounding space
            if(preg_match('/[S|W]/',$cc)) {
                $cc = preg_replace('/\s*[S|W]\s*/','',$cc);
                $mult = -1;
            }

            preg_match("/deg[:]?\s*([\-|\+]?\d{1,3})[°|\s]*min[:]?\s*(\d{1,2})['|\s]*sec[:]?\s*(\d+(\.\d+)?)[\"]?/i",$cc,$m);
            if(count($m) > 0) {
                $d = DMS::convDMStoDec($m[1],$m[2],$m[3]);
            } else {
                preg_match("/^\D*([\-|\+]?\d{1,3})\s?[°|deg]?\s*(\d+)(\.\d+)?\D*$/i",$cc,$m);
                if(count($m) > 0) {
                    $d = DMS::convDMStoDec($m[1],$m[2],$m[3]*60);
                } else {
                    preg_match("/([\-|\+]?\d{1,3})\s?[°|deg]?\s*(\d{1,2})\s?['|m|min|\.]?\s*(\d+(\.\d+)?)\s?[\"|s|sec]?/i",$cc,$m);
                    if(count($m) > 0) {
                        $d = DMS::convDMStoDec($m[1],$m[2],$m[3]);
                    } else {
                        $d = floatval($cc);
                    }
                }
            }
            // if both $d and $mult are negative, set $mult positive
            if(($d > 0) && ($mult == -1)) {
                $d = $d * $mult;
            }
        } else {
            throw new \Exception(__METHOD__." invalid or un-parseable coordinate value!");
        }
        return $d;
    }
}
