<?php
/**
 * File name or class: brief description goes here
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */
 
class Geo_Latitude {
    /**
     * @var float
     */
    public $val;

    public function __construct($i=null) {
        if(!(is_null($i))) {
            $this->parseVal($i);
        }
    }

    public function getAsDMS() {
        $dms = new Geo_DMS($this);
        return $dms->getAsString();
    }

    public function parseVal($i) {
        if(is_string($i)) {
            // assume positive
            $mult = 1;
            // north matches positive so remove 'N' and any surrounding space
            $d = preg_replace('/\s*N\s*/','',$i);
            // if includes 'S', set multiplier to negative and remove the S and any surrounding space
            if(preg_match('/S/',$i)) {
                $d = preg_replace('/\s*S\s*/','',$i);
                $mult = -1;
            }

            preg_match("/deg[:]?\s*([\-|\+]?\d{1,3})[°|\s]*min[:]?\s*(\d{1,2})['|\s]*sec[:]?\s*(\d+(\.\d+)?)[\"]?/i",$i,$m);
            if(count($m) > 0) {
                $d = Geo_DMS::convDMStoDec($m[1],$m[2],$m[3]);
            } else {
                preg_match("/([\-|\+]?\d{1,3})\s?[°|deg]*\s*(\d{1,2})\s?['|m|min]\s*(\d+(\.\d+)?)\s?[\"|s|sec]/i",$i,$m);
                if(count($m) > 0) {
                    $d = Geo_DMS::convDMStoDec($m[1],$m[2],$m[3]);
                } else {
                    $d = floatval($i);
                }
            }
        } elseif(!(is_int($d) || is_float($d))) {
            throw new Exception(__METHOD__." invalid or un-parseable latitude value!");
        }

        // if both $d and $mult are negative, set $mult positive
        if($i !== 0) {
            $mult = $mult * ($d / abs($d));
        }
        $d = $d * $mult;

        if(($d >= -90 ) && ($d <= 90)) {
            $this->val = $d;
            return $this->val;
        } else {
            return false;
        }
    }
}

class Geo_Longitude {
    /**
     * @var float
     */
    public $val;

    public function __construct($i=null) {
        if(!(is_null($i))) {
            $this->parseVal($i);
        }
    }

    public function getAsDMS() {
        $dms = new Geo_DMS($this);
        return $dms->getAsString();
    }

    public function parseVal($i) {
        if(is_string($i)) {
            // assume positive
            $mult = 1;
            // east matches positive so remove 'E' and any surrounding space
            $i = preg_replace('/\s*E\s*/','',$i);
            // if includes 'W', set multiplier to negative and remove the W and any surrounding space
            if(preg_match('/W/',$i)) {
                $i = preg_replace('/\s*W\s*/','',$i);
                $mult = -1;
            }

            preg_match("/deg[:]?\s*([\-|\+]?\d{1,3})[°|\s]*min[:]?\s*(\d{1,2})['|\s]*sec[:]?\s*(\d+(\.\d+)?)[\"]?/i",$i,$m);
            if(count($m)>0) {
                $dms = new Geo_DMS();
                $i = Geo_DMS::convDMStoDectoDec($m[1],$m[2],$m[3]);
            } else {
                preg_match("/([\-|\+]?\d{1,3})\s?[°|deg]*\s*(\d{1,2})\s?['|m|min]\s*(\d+(\.\d+)?)\s?[\"|s|sec]/i",$i,$m);
                if(count($m)>0) {
                    $i = Geo_DMS::convDMStoDec($m[1],$m[2],$m[3]);
                } else {
                    $i = floatval($i);
                }
            }
        } elseif(!(is_int($i) || is_float($i))) {
            throw new Exception(__METHOD__." invalid or un-parseable latitude value!");
        }

        // if both $i and $mult are negative, set $mult positive
        if($i !== 0) {
            $mult = $mult * $i / abs($i);
        }
        $i = $i * $mult;

        if(($i >= -180 ) && ($i <= 180)) {
            $this->val = $i;
            return $this->val;
        } else {
            return false;
        }
    }
}

class Geo_DMS {
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
     * @param Geo_Latitude|Geo_Longitude $o
     * @see setFromLL()
     */
    public function __construct($d=null,$m=null,$s=null,$ch=null) {
        if(isset($d)) {
            if(($d instanceof Geo_Latitude) || ($d instanceof Geo_Longitude)) {
                $this->setFromLL($d);
            } elseif(isset($ch)) {
                $this->degrees = intval($d);
                $this->minutes = intval($m);
                $this->seconds = floatval($s);
                $this->compass = "$ch";
            } else {
                throw new Exception(__METHOD__." if degrees is specified, compass heading is also required to init!");
            }
        }
    }

    /**
     * set values from an instance of a latitude or longitude
     * @param Geo_Latitude|Geo_Longitude $ll
     * @throws Exception if param is not an instance of Geo_Latitude or Geo_Longitude
     */
    public function setFromLL($ll) {
        if($ll instanceof Geo_Latitude) {
            $this->compass = ($ll->val < 0) ? 'S' : 'N';
        } elseif($ll instanceof Geo_Longitude) {
            $this->compass = ($ll->val < 0) ? 'W' : 'E';
        } else {
            throw new Exception(__METHOD__." requires input of a Geo_Latitude or Geo_Longitude instance!");
        }

        $dec = abs($ll->val);
        $this->degrees = intval($dec);
        $_md = ($dec - $this->degrees) * 60;
        $this->minutes = intval($_md);
        $this->seconds = floatval(sprintf("%.4f",($_md - $this->minutes) * 60));
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
     * return the values of an instance of Geo_DMS as a formatted lat/lon string
     * @static
     * @param Geo_DMS|integer $deg
     * @param null|integer $min
     * @param null|float $sec
     * @param null|string $ch
     * @return string
     * @throws Exception if $deg (and $ch if $deg is not a populated instance of Geo_DMS) are not set
     */
    public static function getDMSAsString($deg,$min=null,$sec=null,$ch=null) {
        $d = null;
        $m = intval($min);
        $s = floatval($sec);
        $c = "$ch";

        if($deg instanceof Geo_DMS) {
            $d = $deg->degrees;
            $m = $deg->minutes;
            $s = $deg->seconds;
            $c = $deg->compass;
        } else {
            $d = intval($deg);
        }

        if(is_null($deg) || ($c === '')) {
            throw new Exception(__METHOD__.' a minimum of degrees and compass must be set');
        }
        if(!preg_match("/$c/","NSEW")) {
            throw new Exception(__METHOD__.' compass must be one of: N, S, E, W!');
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
     * @param Geo_DMS|integer $deg
     * @param null|integer $min
     * @param null|float $sec
     * @param null|string $ch
     * @return float
     */
    public static function getDMSAsDec($deg=null,$min=null,$sec=null,$ch=null) {
        $c = "$ch";

        if($deg instanceof Geo_DMS) {
            $min = $deg->minutes;
            $sec = $deg->seconds;
            $ch  = $deg->compass;
            $deg = $deg->degrees;
        }

        $mult = (($ch === 'N') || ($ch === 'W')) ? -1.0 : 1.0;
        return $mult * self::convDMStoDec($deg,$min,$sec);
    }

    public static function convDMStoDec($d,$m=null,$s=null) {
        return 1.0 * (intval($d) + (intval($m)/60) + (floatval($s)/3600));
    }
}

class Geo_Coordinate {
    /**
     * @var float Geo_Latitude
     */
    public $lat;
    /**
     * @var float Geo_Longitude
     */
    public $lon;

    public function __construct($latitude=null,$longitude=null) {
        if(isset($latitude) && isset($longitude)) {
            $this->lat = new Geo_Latitude($latitude);
            $this->lon = new Geo_Longitude($longitude);
        }
    }
}
