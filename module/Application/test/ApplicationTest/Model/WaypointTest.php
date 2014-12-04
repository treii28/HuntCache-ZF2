<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Waypoint;
use \Application\Mapper\WaypointMapper;
use \PHPUnit_Framework_TestCase;

class WaypointTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testWaypointModel()
    {
        $at = new Waypoint();
        $this->assertInstanceOf('\Application\Model\Waypoint', $at);
    }

}
 