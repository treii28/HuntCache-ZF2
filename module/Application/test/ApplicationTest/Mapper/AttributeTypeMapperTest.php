<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\AttributeType;
use \Application\Mapper\AttributeTypeMapper;
use \PHPUnit_Framework_TestCase;

class AttributeTypeMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testAttributeTypeMapper()
    {
        $at = new AttributeTypeMapper();
        $this->assertInstanceOf('\Application\Mapper\AttributeTypeMapper', $at);
    }

}
 