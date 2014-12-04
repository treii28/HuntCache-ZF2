<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 12/4/14
 * Time: 11:13 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\Country;
use \Application\Mapper\CountryMapper;
use \PHPUnit_Framework_TestCase;

class CountryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group datatype-model
     */
    function testCountryModel()
    {
        $at = new Country();
        $this->assertInstanceOf('\Application\Model\Country', $at);
    }

}
 