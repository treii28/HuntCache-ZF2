<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 3:15 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Mapper;

class TitleMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'titles';
    const MODEL_NAME	= 'Application\Model\Title';
    const DEFAULT_ID = 1; // 1 = none
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    public static function titleExists(Application\Model\Title $data) {
        $model = self::_getDataType();
        $idName = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idName);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $rec = self::getTitleByName($data->getName());
            return (boolean) ($rec instanceof $model);
        } elseif((string) $data->getDescription() !== '') {
            $rec = self::getTitleByDescription($data->getDescription());
            return (boolean) ($rec instanceof $model);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    public static function getTitleByName($name) {
        $name = (string) $name;
        if($name !== '') {
            $table = self::_getDbTable();
            $where = $table->getAdapter()->quoteinto('name = ?',$name);
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
            throw new \Exception(__METHOD__." name '$name' must be a string");
        }
    }

    public static function getTitleByDescription($desc) {
        $desc = (string) $desc;
        if($desc !== '') {
            $table = self::_getDbTable();
            $where = $table->getAdapter()->quoteinto('description = ?',$desc);
            $rs = $table->fetchAll(
                $table->select()
                        ->where($where)
            );

            if(count($rs)==1) {
                $row = $rs->current();
                return self::_createModelFromRow($row);
            } else {
                return false;
                //throw new \Exception(__METHOD__." row not found for description = '$desc'");
            }
        } else {
            throw new \Exception(__METHOD__." description '$desc' must be a string");
        }
    }

    public static function getTitleById($id) {
        $id = intval($id);
        if(!($id>0)) {
            $id = self::DEFAULT_ID;
        }

        return parent::getModelById($id);
    }

    public static function getTitleList() {
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

    public static function getTitleNames() {
        $model = self::_getDataType();
        $pId   = $model::_getPrimaryIdKey();
        $LblNm = $model::_getLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($pId,$LblNm)
        );
        $names = array();
        foreach($rs as $_r) {
            $names[$_r->$pId] = $_r->$LblNm;
        }
        return $names;
    }

    public static function autoDefine($data) {
        if($data instanceof Application\Model\Title) {
            return $data;
        } elseif(is_array($data)) {
            $newTitle = new Application\Model\Title($data);
            return $newTitle;
        } elseif(intval($data) > 0) {
            return self::getTitleById($data);
        } elseif(is_string($data)) {
            return self::getTitleByName($data);
        } else {
            throw new \Exception(__METHOD__." unable to determine data type for title");
        }
    }
}
