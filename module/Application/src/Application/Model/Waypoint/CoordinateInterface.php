<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 12/11/12
 * Time: 5:06 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model\Waypoint;

interface CoordinateInterface
{
    public function setCoord($lat=null,$lon=null);
    public function getCoord();
    public function setLat($lat);
    public function getLat();
    public function setLong($lon);
    public function getLong();
}
