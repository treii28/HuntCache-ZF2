<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\AccountType;
use \Application\Mapper\AccountTypeMapper;
use \PHPUnit_Framework_TestCase;

class AccountTypeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testAccountTypeModel()
    {
        $at = new AccountType();
        $this->assertInstanceOf('\Application\Model\AccountType', $at);
    }

}
 