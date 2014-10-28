<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

class Application\Model\Track_Leg
{
    // A number of functions need to know the mean radius of the Earth for its
    //  calculations. You need to set this constant to that value, in whatever
    //  unit you wish the calculations to be carried out in.  For reference, it
    //  is 6371m; however we will use its value in miles.
    const EARTH_R = 3956.09;

    /**
     * @var Application\Model\Waypoint_Coordinate $start
     */
    public $start;
    /**
     * @var Application\Model\Waypoint_Coordinate $end
     */
    public $end;

    public function __construct(Application\Model\Waypoint_Coordinate $start=null, Application\Model\Waypoint_Coordinate $end=null) {
        if(isset($start)) {
            $this->start = $start;
        }
        if(isset($end)) {
            $this->end = $end;
        }
    }
    public function getDistance() {
        return self::geoDistance($this->start,$this->end);
    }

    /**
     * Calculate the distance between two points
     * @param Application\Model\Waypoint_Coordinate $c1  coordinate 1
     * @param Application\Model\Waypoint_Coordinate $c2  coordinate 2
     * @param string $unit  unit of distances measurement (k,m,f,y,s)
     * 		k = kilometers
     * 		m = meters
     * 		f = feet
     * 		y = yards
     * 		s or null = default statute miles
     * @return float value based on unit type
     */
    public static function geoDistance (Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2, $unit="s")
    {
        $theta = $c1->lon->getAsRad() - $c2->lon->getAsRad();
        $dist = sin($c1->lat->getAsRad()) * sin($c2->lat->getAsRad()) +
                cos($c1->lat->getAsRad()) * cos($c2->lat->getAsRad()) *
                        cos($theta);
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtolower($unit);
        switch($unit) {
            case "k":
                return ($miles * 1.609344);
            case "m":
                return ($miles * 1607.344);
            case "f":
                return ($miles * 5280);
            case "y":
                return ($miles * 5280/3);
            default:
                return $miles;
        }
    }

    public function getDistanceHv() {
        return self::geoDistanceHv($this->start,$this->end);
    }

    public static function geoDistanceHv(Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2, $unit="s") {
        $R = 6371; // km
        $lat1 = $c1->lat->getAsRad();
        $lat2 = $c2->lat->getAsRad();
        $lon1 = $c1->lon->getAsRad();
        $lon2 = $c2->lon->getAsRad();

        $a = pow(sin(($lat2-$lat1)/2),2) + pow(sin(($lon2-$lon1)/2),2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = self::EARTH_R * $c;
        return $d;
    }

    public function getDistanceGC() {
        return self::geoDistanceGC($this->start,$this->end);
    }

    // Function: geoDistanceGC
    // Desc:  Calculate the shortest distance between two pairs of coordinates.
    //   This calculates a great arc around the Earth, assuming that the Earth
    //   is a sphere.  There is some error in this, as the earth is not
    //   perfectly a sphere, but it is fairly accurate.
    public static function geoDistanceGC(Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2) {
        // Perform the formula and return the value
        return acos(
            ( sin($c1->lat->getAsRad()) * sin($c2->lat->getAsRad()) ) +
                    ( cos($c1->lat->getAsRad()) * cos($c2->lat->getAsRad()) * cos($c2->lon->getAsRad() - $c1->lon->getAsRad()) )
        ) * self::EARTH_R;
    }

    public function getBearingGC() {
        return self::geoBearingGC($this->start,$this->end);
    }
    // Function: latlon_bearing_great_circle
    // Desc:  This function calculates the initial bearing you need to travel
    //   from Point A to Point B, along a great arc.  Repeated calls to this
    //   could calculate the bearing at each step of the way.
    public static function geoBearingGC(Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2) {
        $rads = atan2(
            sin($c2->lon->getAsRad() - $c1->lon->getAsRad()) * cos($c2->lat->getAsRad()),
                (cos($c1->lat->getAsRad() ) * sin($c2->lat->getAsRad() )) -
                        (sin($c1->lat->getAsRad() ) * cos($c2->lat->getAsRad() ) * cos($c2->lon->getAsRad()  - $c1->lon->getAsRad() )) );

        // Convert this back to degrees to use with a compass
        $degrees = Application\Model\Waypoint_Coordinate_DMS::convRadtoDeg($rads);

        // If negative subtract it from 360 to get the bearing we are used to.
        $degrees = ($degrees < 0) ? 360 + $degrees : $degrees;

        return $degrees;
    }

    public function getDistanceRhumb() {
        return self::geoDistanceRhumb($this->start,$this->end);
    }

    // Function: geoDistanceRhumb
    // Desc:  Calculates the distance between two points along a Rhumb line.
    //   Rhumb lines are a line between two points that uses a constant
    //   bearing.  They are slightly longer than a great circle path; however,
    //   much easier to navigate.
    public static function geoDistanceRhumb(Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2) {
        // First of all if this a true East/West line there is a special case:
        if ($c1->lat->getAsRad()  == $c2->lat->getAsRad() ) {
            $mid = cos($c1->lat->getAsRad() );
        } else {
            $delta = log( tan(($c2->lat->getAsRad()  / 2) + (M_PI / 4))
                    / tan(($c1->lat->getAsRad()  / 2) + (M_PI / 4)) );
            $mid = ($c2->lat->getAsRad()  - $c1->lat->getAsRad() ) / $delta;
        }

        // Calculate difference in longitudes, and if over 180, go the other
        //  direction around the Earth as it will be a shorter distance:
        $dlon = abs($c2->lon->getAsRad()  - $c1->lon->getAsRad() );
        $dlon = ($dlon > M_PI) ? (2 * M_PI - $dlon) : $dlon;
        $distance = sqrt( pow($c2->lat->getAsRad()  - $c1->lat->getAsRad() ,2) +
                (pow($mid, 2) * pow($dlon, 2)) ) * self::EARTH_R;

        return $distance;
    }

    public function getBearingRhumb() {
        return self::geoBearingRhumb($this->start,$this->end);
    }
    // Function: geoBearingRhumb
    // Desc:  Calculates the bearing for the Rhumb line between two points.
    public static function geoBearingRhumb(Application\Model\Waypoint_Coordinate $c1, Application\Model\Waypoint_Coordinate $c2) {
        // Perform the math & store the values in radians.
        $delta = log( tan(($c2->lat->getAsRad()  / 2) + (M_PI / 4))
                / tan(($c1->lat->getAsRad()  / 2) + (M_PI / 4)) );
        $rads = atan2( ($c2->lon->getAsRad()  - $c1->lon->getAsRad() ), $delta);

        // Convert this back to degrees to use with a compass
        $degrees = Application\Model\Waypoint_Coordinate_DMS::convRadtoDeg($rads);

        // If negative subtract it from 360 to get the bearing we are used to.
        $degrees = ($degrees < 0) ? 360 + $degrees : $degrees;

        return $degrees;
    }
}
