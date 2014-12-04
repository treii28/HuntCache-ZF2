<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\User;
use \Application\Mapper\UserMapper;
use \PHPUnit_Framework_TestCase;

class UserMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testUserMapper()
    {
        $at = new UserMapper();
        $this->assertInstanceOf('\Application\Mapper\UserMapper', $at);
    }

}
 