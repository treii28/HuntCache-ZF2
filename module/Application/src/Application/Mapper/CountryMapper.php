<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/8/11
 * Time: 5:31 PM
 * To change this template use File | Settings | File Templates.
 */

class Application\Model\CountryMapper extends Application\Model\MapperAbstract
{
    const DB_TABLE_NAME  = 'countries';
    const MODEL_NAME     = 'Application\Model\Country';
    const DEFAULT_ID = 223;

    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    public static function countryExists(Application\Model\Country $data) {
        $pId = $data->getId();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            return (boolean) (self::getCountryByName($data->getName()) instanceof Application\Model\Country);
        } elseif((string) $data->getFips() !== '') {
            return (boolean) (self::getCountryByFips($data->getFips()) instanceof Application\Model\Country);
        } elseif(((int) $data->getCountry_2_code() > 0)) {
            return (boolean) (self::getCountryByCode($data->getCountry_2_code()) instanceof Application\Model\Country);
        } elseif(((int) $data->getCountry_3_code() > 0)) {
            return (boolean) (self::getCountryByCode($data->getCountry_3_code()) instanceof Application\Model\Country);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    public static function getCountryByName($name) {
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

    public static function getCountryById($id=null) {
        $id = intval($id);
        if(!($id>0)) {
            $id = self::DEFAULT_ID;
        }

        return parent::getModelById($id);
    }

    public static function getCountryByCode($code) {
        $table = self::_getDbTable();
        $select = $table->select();
        if(strlen($code) === 2) {
            $select->where('country_2_code = ?', $code);
        } elseif(strlen($code) === 3) {
            $select->where('country_3_code = ?', $code);
        } else {
            throw new \Exception(__METHOD__." code '$code' must be 2 or 3 characters");
        }

        $row = $table->fetchRow($select);
        if($row instanceof Zend_Db_Table_Row) {
            return self::_createModelFromRow($row);
        } else {
            return false;
        }
    }

    public static function getCountryByFips($fips) {
        $table = self::_getDbTable();
        $row = $table->fetchRow(
            $table->select()
                ->where('fips = ?', $fips)
        );
        if($row instanceof Zend_Db_Table_Row) {
            return self::_createModelFromRow($row);
        } else {
            return false;
        }
    }

    public static function getCountryList() {
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

    public static function getCountryNames() {
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
        if($data instanceof Application\Model\Country) {
            return $data;
        } elseif(is_array($data)) {
            $newCountry = new Application\Model\Country($data);
            return $newCountry;
        } elseif(intval($data) > 0) {
            return self::getCountryById($data);
        } elseif(is_string($data)) {
            if(strlen($data) > 3) {
                return self::getCountryByName($data);
            } else {
                return self::getCountryByCode($data);
            }
        } else {
            // returns default country for null
            return self::getCountryById();
        }
    }
}
