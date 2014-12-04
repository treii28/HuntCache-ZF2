<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\Attribute;
use \Application\Mapper\AttributeMapper;
use \PHPUnit_Framework_TestCase;

class AttributeMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testAttributeMapper()
    {
        $at = new AttributeMapper();
        $this->assertInstanceOf('\Application\Mapper\AttributeMapper', $at);
    }

}
 