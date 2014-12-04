<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Title;
use \Application\Mapper\TitleMapper;
use \PHPUnit_Framework_TestCase;

class TitleTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testTitleModel()
    {
        $at = new Title();
        $this->assertInstanceOf('\Application\Model\Title', $at);
    }

}
 