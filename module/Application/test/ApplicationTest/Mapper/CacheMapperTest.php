<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\Cache;
use \Application\Mapper\CacheMapper;
use \PHPUnit_Framework_TestCase;

class CacheMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testCacheMapper()
    {
        $at = new CacheMapper();
        $this->assertInstanceOf('\Application\Mapper\CacheMapper', $at);
    }

}
 