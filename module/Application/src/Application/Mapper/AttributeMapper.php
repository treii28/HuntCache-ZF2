<?php

namespace Application\Model;

class AttributeMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'attributes';
    const MODEL_NAME	= 'Application\Model\Attribute';
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    public static function _initDb() {
        $tbl = self::_getDbTable();
        foreach(Attribute::$VALID_ATTRIBUTE_NAMES as $v) {
            $attr = new Attribute();
            $attr->setName($v);
            $attr->setDescription(sprintf("Open Cache Format attribute %s description", $v));

            if(! (self::attributeExists($attr)) ) {
                self::save($attr);
            }
        }
    }

    /**
     * checks to see if records for a given id exist in the database
     * @param int $id
     * @return boolean  returns true if record of id exists
     */
    public static function attributeExists(Attribute $data) {
        $model  = self::_getDataType();
        $pId = $data->getId();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $att = self::getAttributeByName($data->getName());
            return (boolean) ($att instanceof Attribute);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    /**
     * retrieve an attribute by it's name
     * @static
     * @throws \Exception for not found
     * @param string $name
     * @return bool|Attribute
     */
    public static function getAttributeByName($name) {
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
    public static function getAttributeById($id) {
        $model = self::_getDataType();
        return parent::getModelById($id);
    }

    // redundant method
    /**
     * retrieve all attributes as array of attribute names
     * @return array
     */
    public static function getAttributeList() {
        $model = self::_getDataType();
        $LblNm = $model::_getLabelName();
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
    public static function getAttributeNames() {
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

    public static function getAttributesByType($type) {
        if((int) $type > 0) {
            $type = AttributeTypeMapper::getAttributeTypeById($type);
        } elseif(is_string($type)) {
            $type = AttributeTypeMapper::getAttributeTypeByName($type);
        }
        if(!($type instanceof AttributeType)) {
            throw new \Exception(__METHOD__." unable to determine Attribute Type");
        }
        $tId = $type->getId();
        $table = self::_getDbTable();
        $where = $table->getAdapter()->quoteInto('attributeTypeId = ?', $tId);
        $rs = $table->fetchAll(
            $table->select()
                    ->where($where)
        );

        $tAttr = array();
        foreach($rs as $ta) {
            array_push($tAttr, self::_createModelFromRow($ta));
        }
        return $tAttr;
    }
}
