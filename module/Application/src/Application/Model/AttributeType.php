<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/2/11
 * Time: 4:49 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

class AttributeType extends ModelAbstract
{
    const PRIMARY_ID_KEY  = 'attributeTypeId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'attributeType';

    protected static $_dataMapperName = 'AttributeTypeMapper';

    /**
     * @var int
     */
    public $attributeTypeId;
    /**
     * @var array
     */
    protected static $_dataStruct = array(
        'name'		=> null,
        'description' => null
    );

    /**
     * @param null|int|array $data
     */
    public function __construct($data=null) {
        if(is_int($data) &&(intval($data)>0)) {
            $pId = self::_getPrimaryIdKey();
            $data = array($pId => intval($data));
        }
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }
}
