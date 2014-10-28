<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/8/11
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */

class Application\Model\StateMapper extends Application\Model\MapperAbstract
{
    /**
     * db table name
     */
    const DB_TABLE_NAME  = 'states';
    /**
     * name of model this mapper corresponds to
     */
    const MODEL_NAME     = 'Application\Model\State';

    protected static $_dbTable = null;

    /**
     * constructor class
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * determine whether or not a state matching a given instance of the model exists
     * @static
     * @param Application\Model\State $data
     * @return bool
     * @throws \Exception
     */
    public static function stateExists(Application\Model\State $data) {
        $model  = self::_getDataType();
        $pId = $data->getId();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $row = self::getStateByName($data->getName());
            return (boolean) ($row instanceof Zend_Db_Table_Row);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    /**
     * retrieve a state by the state name
     * @static
     * @param $name
     * @return bool|object
     * @throws \Exception
     */
    public static function getStateByName($name) {
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
     * retrieve a state by the state id
     * @static
     * @param integer $id
     * @return __CLASS__
     */
    public static function getStateById($id) {
        $model = self::_getDataType();
        return parent::getModelById($id);
    }

    /**
     * retrieve a state by the corresponding state code
     * @static
     * @param $code
     * @return bool|object
     * @throws \Exception
     */
    public static function getStateByCode($code) {
        $table = self::_getDbTable();
        $select = $table->select();
        if(is_string($code)) {
            if(strlen($code) === 2) {
                $select->where('state_2_code = ?', $code);
            } elseif(strlen($code) === 3) {
                $select->where('state_3_code = ?', $code);
            } else {
                throw new \Exception(__METHOD__." code '$code' must be 2 or 3 characters");
            }
        } elseif(intval($code) > 0) {
            $select->where('num_code = ?', $code);
        } else {
            throw new \Exception(__METHOD__." code '$code' not recognized");
        }

        $row = $table->fetchRow($select);
        if($row instanceof Zend_Db_Table_Row) {
            return self::_createModelFromRow($row);
        } else {
            return false;
        }
    }

    /**
     * get a full list of countries with any states nested under the country in an array
     *
     * @return array
     */
    public static function getCountryStateValues() {
        $countryState = array();
        foreach(self::getAll() as $_st)
        {
            $_ct = $_st->getCountry();
            $_ctId = $_ct->getId();
            $countryState[$_ctId]['name'] = $_ct->getName();
            $_stId = $_st->getId();
            $countryState[$_ctId]['states'][$_stId] = $_st->getName();
        };
        return $countryState;
    }

    /**
     * retrieve a list of state names based on country id
     * @static
     * @param integer $countryId
     * @return array   of state names keyed by id
     */
    public static function getStatesByCountryId($countryId) {
        $table = self::_getDbTable();
        $select = $table->select();
        $select->where('countryId = ?',$countryId);

        $rs = $table->fetchAll($select);

        $stateNames = array();
        foreach($rs as $row) {
            $stateNames[$row->stateId] = $row->name;
        }
        return $stateNames;
    }

    // redundant function
    /**
     * retrieve the full list of state label names as an array
     * @static
     * @return array  of State labels
     */
    public static function getStateList() {
        $LblNm = self::_getModelLabelName();
        $table = self::_getDbTable();
        $rs = $table->fetchAll(
            $table->select($LblNm)
        );
        $labels = array();
        foreach($rs as $row) {
            $labels[$row->stateId] = $row->$LblNm;
        }
        return $labels;
    }

    /**
     * get a list of the names keyed by id
     * @static
     * @return array  of state names keyed by stateId
     */
    public static function getStateNames() {
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

    /**
     * try to automatically set the type based on given data
     * @static
     * @param $data
     * @return __CLASS__|Application\Model\State|bool|object
     * @throws \Exception  if unable to determine state type
     */
    public static function autoDefine($data) {
        if($data instanceof Application\Model\State) {
            return $data;
        } elseif(is_array($data)) {
            $newState = new Application\Model\State($data);
            return $newState;
        } elseif(intval($data) > 0) {
            return self::getStateById($data);
        } elseif(is_string($data)) {
            if(strlen($data) > 3) {
                return self::getStateByName($data);
            } else {
                return self::getStateByCode($data);
            }
        } else {
            throw new \Exception(__METHOD__." unable to determine data type for state");
        }
    }
}