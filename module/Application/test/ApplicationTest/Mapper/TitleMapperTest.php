<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\Title;
use \Application\Mapper\TitleMapper;
use \PHPUnit_Framework_TestCase;

class TitleMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testTitleMapper()
    {
        $at = new TitleMapper();
        $this->assertInstanceOf('\Application\Mapper\TitleMapper', $at);
    }

}
 