<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Track;
use \Application\Mapper\TrackMapper;
use \PHPUnit_Framework_TestCase;

class TrackTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testTrackModel()
    {
        $at = new Track();
        $this->assertInstanceOf('\Application\Model\Track', $at);
    }

}
 