<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Cache;
use \Application\Mapper\CacheMapper;
use \PHPUnit_Framework_TestCase;

class CacheTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testCacheModel()
    {
        $at = new Cache();
        $this->assertInstanceOf('\Application\Model\Cache', $at);
    }

}
 