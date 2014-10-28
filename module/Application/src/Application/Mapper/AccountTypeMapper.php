<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 1:21 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Mapper;

class AccountTypeMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'accountTypes';
    const MODEL_NAME    = 'AccountType';
    const DEFAULT_ID = 6; // 4 = user, 6 = guest
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    public static function accountTypeExists(\Application\Model\AccountType $data) {
        $model  = self::_getDataType();
        $idName = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idName);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $rec = self::getAccountTypeByName($data->getName());
            return (boolean) ($rec instanceof $model);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    public static function getAccountTypeByName($name) {
        $name = (string) $name;
        if($name !== '') {
            $table = self::_getDbTableGateway();
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
                //throw new \Exception(__METHOD__." row not found for name '$name'");
            }
        } else {
            throw new \Exception(__METHOD__." name '$name' must be string");
        }
    }

    public static function getAccountTypeById($id=null) {
        $id = intval($id);
        if(!($id>0)) {
            $id = self::DEFAULT_ID;
        }

        return parent::getModelById($id);
    }

    public function getAccountTypeList() {
        $model = self::_getDataType();
        $LblNm = $model::_getLabelName();
        $table = self::_getDbTableGateway();
        $rs = $table->fetchAll(
            $table->select($LblNm)
        );
        $labels = array();
        foreach($rs as $row) {
            array_push($labels,$row->$LblNm);
        }
        return $labels;
    }

    public static function getAccountTypeNames() {
        $model = self::_getDataType();
        $pId   = $model::_getPrimaryIdKey();
        $LblNm = $model::_getLabelName();
        $table = self::_getDbTableGateway();
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
        if($data instanceof \Application\Model\AccountType) {
            return $data;
        } elseif(is_array($data)) {
            $newAccountType = new \Application\Model\AccountType($data);
            return $newAccountType;
        } elseif(intval($data) > 0) {
            return self::getAccountTypeById($data);
        } elseif(is_string($data)) {
            return self::getAccountTypeByName($data);
        } else {
            // returns default type for null
            return self::getAccountTypeById();
        }
    }
}
