<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

class Application\Model\Track // extends Application\Model\ModelAbstract
{
    const PRIMARY_ID_KEY   = 'trackId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'track';

    //protected static $_dataMapperName = 'TrackMapper';

    protected $trackid;
    public $coordinates = array();

    /**
     * append a waypoint to the list of coordinates
     * @param array|Application\Model\Waypoint $data   array of key/value pairs or a Waypoint object
     */
    public function addWaypoint(Application\Model\Waypoint_Coordinate $coord) {
            array_push($this->coordinates, $coord);
        return $this->coordinates;
    }

    public function getLegs() {
        if(count($this->coordinates) < 2) {
            return array();
        }

        $legs = array();
        for($x=1;$x<count($this->coordinates);$x++) {
            array_push($legs,new Application\Model\Track_Leg($this->coordinates[$x-1],$this->coordinates[$x]));
        }

        return $legs;
    }

    public function getTotalDistance() {
        $tDist = 0;
        foreach($this->getLegs() as $leg) {
            $tDist += $leg->getDistance();
        }
        return $tDist;
    }

    public function getDistanceToStart(Application\Model\Waypoint_Coordinate $loc) {
        if(count($this->coordinates) === 0) {
            throw new \Exception(__METHOD__." no waypoints set!");
        }
        return Application\Model\Track_Leg::geoDistance($loc,$this->coordinates[0]);
    }
    public function getBearingToStart(Application\Model\Waypoint_Coordinate $loc) {
        if(count($this->coordinates) === 0) {
            throw new \Exception(__METHOD__." no waypoints set!");
        }
        return Application\Model\Track_Leg::geoBearingGC($loc,$this->coordinates[0]);
    }
}
