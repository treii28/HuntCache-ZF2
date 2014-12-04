<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\Waypoint;
use \Application\Mapper\WaypointMapper;
use \PHPUnit_Framework_TestCase;

class WaypointMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testWaypointMapper()
    {
        $at = new WaypointMapper();
        $this->assertInstanceOf('\Application\Mapper\WaypointMapper', $at);
    }

}
 