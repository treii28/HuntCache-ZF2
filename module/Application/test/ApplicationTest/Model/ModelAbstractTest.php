<?php
/**
 * Created by PhpStorm.
 * User: swood
 * Date: 10/28/2014
 * Time: 1:35 AM
 */

namespace ApplicationTest\Model;

use \Application\Model\ModelAbstract;
use \PHPUnit_Framework_TestCase;

class ModelAbstractTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group abstract-classes
     */
    function testModelAbstract() {
        $inf = new Info();
        $this->assertInstanceOf('\Application\Model\ModelAbstract',$inf);
        $this->assertEquals('infoId',$inf->_getPrimaryIdKey());
        $this->assertEquals('name',$inf->_getLabelName());
        $this->assertEquals('info',$inf->_getDataTypeName());
        //$this->assertEquals('InfoMapper',$inf->_getMapperName());
        $inf->setDescription('this is a description');
        //$this->assertEquals('this is a description', $inf->getDescription());
        $foo = "something";
    }
}

class Info extends ModelAbstract
{
    const PRIMARY_ID_KEY  = 'infoId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'info';
    protected static $_dataMapperName = 'InfoMapper';

    public $titleId;
    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }
}