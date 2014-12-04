<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\State;
use \Application\Mapper\StateMapper;
use \PHPUnit_Framework_TestCase;

class StateTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testStateModel()
    {
        $at = new State();
        $this->assertInstanceOf('\Application\Model\State', $at);
    }

}
 