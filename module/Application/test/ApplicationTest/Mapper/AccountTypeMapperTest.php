<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\AccountType;
use \Application\Mapper\AccountTypeMapper;
use \PHPUnit_Framework_TestCase;

class AccountTypeMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testAccountTypeMapper()
    {
        $at = new AccountTypeMapper();
        $this->assertInstanceOf('\Application\Mapper\AccountTypeMapper', $at);
    }

}
 