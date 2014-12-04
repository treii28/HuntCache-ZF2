<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/2/11
 * Time: 4:49 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Mapper;

class AttributeTypeMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'attributeTypes';
    const MODEL_NAME	= 'Application\Model\AttributeType';
    const DEFAULT_ID  = 1;

    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * checks to see if records for a given id exist in the database
     * @static
     * @param AttributeType $data
     * @return boolean  returns true if record of id exists
     * @throws \Exception
     */
    public static function attributeTypeExists(AttributeType $data) {
        $model  = self::_getDataType();
        $idName = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idName);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $rec = self::getAttributeTypeByName($data->getName());
            return (boolean) ($rec instanceof $model);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    public static function getAttributeTypeByName($name) {
        $name = (string) $name;
        if($name !== '') {
            $table = self::_getDbTable();
            $where = $table->getAdapter()->quoteInto('name = ?', $name);
            $rs = $table->fetchAll(
                $table->select()
                        ->where($where)
            );

            if(count($rs)==1) {
                $row = $rs->current();
                return self::_createModelFromRow($row);
            } else {
                return false;
                //throw new \Exception(__METHOD__." row not found for name = '$name'");
            }
        } else {
            throw new \Exception(__METHOD__." name '$name' must be string");
        }
    }

    /**
     * Retrieve an attribute by primary id
     * @param integer $id
     * @throws Exception if not found
     * @return Attribute
     */
    public static function getAttributeTypeById($id=null) {
        $id = intval($id);
        if(!($id>0)) {
            $id = self::DEFAULT_ID;
        }

        return parent::getModelById($id);
    }

    // redundant method
    /**
     * retrieve all attributes as array of attribute names
     * @return array
     */
    public static function getAttributeTypeList() {
        $model = self::_getDataType();
        $LblNm   = $model::_getLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($LblNm)
        );
        $labels = array();
        foreach($rs as $row) {
            array_push($labels, $row->$LblNm);
        }
        return $labels;
    }

    /**
     * @static
     * @return array
     */
    public static function getAttributeTypeNames() {
        $model = self::_getDataType();
        $pId   = $model::_getPrimaryIdKey();
        $LblNm = $model::_getLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($pId,$LblNm)
        );
        $names = array();
        foreach ($rs as $_r) {
            $names[$_r->$pId] = $_r->$LblNm;
        }
        return $names;
    }

    public static function autoDefine($data) {
        if($data instanceof AttributeType) {
            return $data;
        } elseif(is_array($data)) {
            $newAttributeType = new AttributeType($data);
            return $newAttributeType;
        } elseif(intval($data) > 0) {
            return self::getAttributeTypeById($data);
        } elseif(is_string($data)) {
            if(strlen($data) > 3) {
                return self::getAttributeTypeByName($data);
            } else {
                return self::getAttributeTypeByCode($data);
            }
        } else {
            // returns default attributeType for null
            return self::getAttributeTypeById();
        }
    }
}
