<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\AttributeType;
use \Application\Mapper\AttributeTypeMapper;
use \PHPUnit_Framework_TestCase;

class AttributeTypeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testAttributeTypeModel()
    {
        $at = new AttributeType();
        $this->assertInstanceOf('\Application\Model\AttributeType', $at);
    }

}
 