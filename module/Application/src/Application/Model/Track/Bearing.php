<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

class Application\Model\Track_Bearing
{
    public $heading;
    public $distance;

    // ranges NOTE: north is a special case
    private static $ranges = array(
        'N'    => array('name'=>"North",'windpoint'=>"Tramontana",'min'=>354.38,'dir'=>0.00,'max'=>5.62),
        'NbE'  => array('name'=>"North by east",'windpoint'=>"Qto Tramontana verso Greco",'min'=>5.63,'dir'=>11.25,'max'=>16.87),
        'NNE'  => array('name'=>"North-northeast",'windpoint'=>"Greco-Tramontana",'min'=>16.88,'dir'=>22.50,'max'=>28.12),
        'NEbN' => array('name'=>"Northeast by north",'windpoint'=>"Qto Greco verso Tramontana",'min'=>28.13,'dir'=>33.75,'max'=>39.37),
        'NE'   => array('name'=>"Northeast",'windpoint'=>"Greco",'min'=>39.38,'dir'=>45.00,'max'=>50.62),
        'NEbE' => array('name'=>"Northeast by east",'windpoint'=>"Qto Greco verso Levante",'min'=>50.63,'dir'=>56.25,'max'=>61.87),
        'ENE'  => array('name'=>"East-northeast",'windpoint'=>"Greco-Levante",'min'=>61.88,'dir'=>67.50,'max'=>73.12),
        'EbN'  => array('name'=>"East by north",'windpoint'=>"Qto Levante verso Greco",'min'=>73.13,'dir'=>78.75,'max'=>84.37),
        'E'    => array('name'=>"East",'windpoint'=>"Levante",'min'=>84.38,'dir'=>90.00,'max'=>95.62),
        'EbS'  => array('name'=>"East by south",'windpoint'=>"Qto Levante verso Scirocco",'min'=>95.63,'dir'=>101.25,'max'=>106.87),
        'ESE'  => array('name'=>"East-southeast",'windpoint'=>"Levante-Scirocco",'min'=>106.88,'dir'=>112.50,'max'=>118.12),
        'SEbE' => array('name'=>"Southeast by east",'windpoint'=>"Qto Scirocco verso Levante",'min'=>118.13,'dir'=>123.75,'max'=>129.37),
        'SE'   => array('name'=>"Southeast",'windpoint'=>"Scirocco",'min'=>129.38,'dir'=>135.00,'max'=>140.62),
        'SEbS' => array('name'=>"Southeast by south",'windpoint'=>"Qto Scirocco verso Ostro",'min'=>140.63,'dir'=>146.25,'max'=>151.87),
        'SSE'  => array('name'=>"South-southeast",'windpoint'=>"Ostro-Scirocco",'min'=>151.88,'dir'=>157.50,'max'=>163.12),
        'SbE'  => array('name'=>"South by east",'windpoint'=>"Qto Ostro verso Scirocco",'min'=>163.13,'dir'=>168.75,'max'=>174.37),
        'S'    => array('name'=>"South",'windpoint'=>"Ostro",'min'=>174.38,'dir'=>180.00,'max'=>185.62),
        'SbW'  => array('name'=>"South by west",'windpoint'=>"Qto Ostro verso Libeccio",'min'=>185.63,'dir'=>191.25,'max'=>196.87),
        'SSW'  => array('name'=>"South-southwest",'windpoint'=>"Ostro-Libeccio",'min'=>196.88,'dir'=>202.50,'max'=>208.12),
        'SWbS' => array('name'=>"Southwest by south",'windpoint'=>"Qto Libeccio verso Ostro",'min'=>208.13,'dir'=>213.75,'max'=>219.37),
        'SW'   => array('name'=>"Southwest",'windpoint'=>"Libeccio",'min'=>219.38,'dir'=>225.00,'max'=>230.62),
        'SWbW' => array('name'=>"Southwest by west",'windpoint'=>"Qto Libeccio verso Ponente",'min'=>230.63,'dir'=>236.25,'max'=>241.87),
        'WSW'  => array('name'=>"West-southwest",'windpoint'=>"Ponente-Libeccio",'min'=>241.88,'dir'=>247.50,'max'=>253.12),
        'WbS'  => array('name'=>"West by south",'windpoint'=>"Qto Ponente verso Libeccio",'min'=>253.13,'dir'=>258.75,'max'=>264.37),
        'W'    => array('name'=>"West",'windpoint'=>"Ponente",'min'=>264.38,'dir'=>270.00,'max'=>275.62),
        'WbN'  => array('name'=>"West by north",'windpoint'=>"Qto Ponente verso Maestro",'min'=>275.63,'dir'=>281.25,'max'=>286.87),
        'WNW'  => array('name'=>"West-northwest",'windpoint'=>"Maestro-Ponente",'min'=>286.88,'dir'=>292.50,'max'=>298.12),
        'NWbW' => array('name'=>"Northwest by west",'windpoint'=>"Qto Maestro verso Ponente",'min'=>298.13,'dir'=>303.75,'max'=>309.37),
        'NW'   => array('name'=>"Northwest",'windpoint'=>"Maestro",'min'=>309.38,'dir'=>315.00,'max'=>320.62),
        'NWbN' => array('name'=>"Northwest by north",'windpoint'=>"Qto Maestro verso Tramontana",'min'=>320.63,'dir'=>326.25,'max'=>331.87),
        'NNW'  => array('name'=>"North-northwest",'windpoint'=>"Maestro-Tramontana",'min'=>331.88,'dir'=>337.50,'max'=>343.12),
        'NbW'  => array('name'=>"North by west",'windpoint'=>"Qto Tramontana verso Maestro",'min'=>343.13,'dir'=>348.75,'max'=>354.37)
    );

    public static function getRange($abbr) {
        if(isset(self::$ranges[$abbr])) {
            return self::$ranges[$abbr];
        } else {
            return false;
        }
    }
    public static function getAbbr($bear) {
        if(is_numeric($bear)) {
            $bear = floatval($bear);
        } else {
            throw new \Exception(__METHOD__."bearing must be a float value!");
        }

        if(($bear >= 0) && ($bear < 360)) {
            // truncate to 2 decimal points
            $bear = (intval($bear * 100))/100;
            // special case for north
            foreach(self::$ranges as $k => $r) {
                if($k === 'N') {
                    if(($bear <= $r['max']) || ($bear >= $r['min'])) {
                        return $k;
                    }
                } else {
                    if(($bear >= $r['min']) && ($bear <= $r['max'])) {
                        return $k;
                    }
                }
            }
            // do stuff
        } else {
            throw new \Exception(__METHOD__." bearing must be from 0 to < 360!");
        }
    }
}
