<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Mapper;

use \Application\Model\Country;
use \Application\Mapper\CountryMapper;
use \PHPUnit_Framework_TestCase;

class CountryMapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-mapper
     */
    function testCountryMapper()
    {
        $at = new CountryMapper();
        $this->assertInstanceOf('\Application\Mapper\CountryMapper', $at);
    }

}
 