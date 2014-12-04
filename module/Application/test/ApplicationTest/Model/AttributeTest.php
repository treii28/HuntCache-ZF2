<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Attribute;
use \Application\Mapper\AttributeMapper;
use \PHPUnit_Framework_TestCase;

class AttributeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testAttributeModel()
    {
        $at = new Attribute();
        $this->assertInstanceOf('\Application\Model\Attribute', $at);
    }

}
 