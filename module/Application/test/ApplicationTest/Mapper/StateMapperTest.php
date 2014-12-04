<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\State;
use \Application\Mapper\StateMapper;
use \PHPUnit_Framework_TestCase;

class StateMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testStateMapper()
    {
        $at = new StateMapper();
        $this->assertInstanceOf('\Application\Mapper\StateMapper', $at);
    }

}
 