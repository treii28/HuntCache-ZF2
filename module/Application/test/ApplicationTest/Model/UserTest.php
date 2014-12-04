<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\User;
use \Application\Mapper\UserMapper;
use \PHPUnit_Framework_TestCase;

class UserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testUserModel()
    {
        $at = new User();
        $this->assertInstanceOf('\Application\Model\User', $at);
    }

}
 