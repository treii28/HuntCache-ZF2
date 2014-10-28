<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 12/5/12
 * Time: 5:58 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model\Waypoint\Coordinate;

class Longitude
{
    /**
     * @var float
     */
    public $val;
    protected $dms;

    public function __construct($i=null) {
        if(!(is_null($i))) {
            $this->parseVal($i);
        }
    }

    public function getDMS() {
        if(!($this->dms instanceof DMS) || ($refresh)) {
            $this->dms = new DMS($this);
        }
        return $this->dms;
    }

    public function getAsDMSString($refresh=false) {
        return $this->getDMS()->getAsString();
    }

    public function getAsRad() {
        return DMS::convDegtoRad($this->val);
    }

    public function parseVal($i) {
        $d = DMS::parseTextCoord($i);

        if(($d >= -180 ) && ($d <= 180)) {
            $this->val = $d;
            return $this->val;
        } else {
            return false;
        }
    }
}
