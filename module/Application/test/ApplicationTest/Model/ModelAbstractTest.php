<?php
/**
 * Created by PhpStorm.
 * User: swood
 * Date: 10/28/2014
 * Time: 1:35 AM
 */

namespace ApplicationTest\Model;

use Application\Mapper\MapperAbstract;
use \Application\Model\ModelAbstract;
use \PHPUnit_Framework_TestCase;

class ModelAbstractTest extends \PHPUnit_Framework_TestCase {

    /**
     * @group abstract-classes
     */
    function testModelAbstract()
    {
        $inData = array(
            'name' => "myInfo",
            'description' => "default description"
        );
        $inf = new Info($inData);
        $iClass = get_class($inf);
        $this->assertEquals($inData['description'],$inf->getDescription());
        $iDataMapper = $inf->_getMapperName();
        $this->assertInstanceOf('\Application\Model\ModelAbstract',$inf);
        $this->assertEquals('infoId',$inf->_getPrimaryIdKey());
        $this->assertEquals('name',$inf->_getLabelName());
        $this->assertEquals('info',$inf->_getDataTypeName());
        //$this->assertEquals('InfoMapper',$inf->_getMapperName());
        $testDesc = 'this is a description';
        $inf->setDescription($testDesc);
        $this->assertEquals($testDesc, $inf->getDescription());
        $testId = 12;
        $setRet = $inf->setId($testId);
        $this->assertEquals($testId,$setRet);
        $this->assertEquals($testId,$inf->getId());
        $data = $inf->getData();
        $this->assertTrue(is_array($data));
        $this->assertEquals(2,count($data));
        $this->assertEquals($testDesc,$data['description']);
        $this->assertEquals($inData['name'],$data['name']);
        $dArr = $inf->asArray();
        $this->assertTrue(is_array($dArr));
        $this->assertTrue(isset($dArr[$testId]));
        $this->assertTrue(is_array($dArr[$testId]));
        $this->assertEquals($testDesc,$dArr[$testId]['description']);
    }
}

class Info extends ModelAbstract
{
    const PRIMARY_ID_KEY  = 'infoId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'info';
    protected static $_dataMapperName = 'InfoMapper';

    public $infoId;

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }
}

class Infomapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'info';
    const MODEL_NAME    = 'Info';
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }
}