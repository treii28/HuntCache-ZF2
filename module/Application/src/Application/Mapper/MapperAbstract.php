<?php

namespace Application\Mapper;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;

abstract class MapperAbstract extends \Application\Model\AbstractClass
{
    protected static $_dbTable;
    const DB_TABLE_NAME = '';

    /**
     * abstract class constructor
     */
    public function __construct() {
        self::_childIsValid();
        parent::__construct();
    }

    /**
     * use _getDbTable's getAdapter() method to get a \Zend\Db object for the default adapter.
     * (this will initialize the class's cached default \Zend\Db\Table instance)
     * @static
     * @return \Zend\Db\Adapter\Adapter
     * @see _getDbTableGateway()
     */
    public static function _getDb() {
        return self::_getDbTableGateway()->getAdapter();
    }

    /**
     * @static
     * @param string $name  alternate table name to override stored name if desired
     * @return TableGateway
     * @throws \Exception
     */
    public static function _getDbTableGateway($name=null) {
        $dataMapper = get_called_class();
        if(!isset($name)) {
            $name = $dataMapper::_getDbTableName();
        }
        if(!isset($dataMapper::$_dbTable)) {
            $dataMapper::$_dbTable = new TableGateway($name);
        }
        if($dataMapper::$_dbTable instanceof TableGateway) {
            return $dataMapper::$_dbTable;
        } else {
            throw new \Exception(__METHOD__." unable to retrieve db table '$name''");
        }
    }

    public static function save($data) {
        $dataType = self::_getDataType();

        if($data instanceof $dataType) {
            $idKey = self::_getModelPrimaryIdKey();
            $store = array(
                $idKey   => $data->getId()
            );
            foreach($data->getData() as $k => $v) {
                $store[$k] = $v;
            }

            $table = self::_getDbTableGateway();
            if(intval($store[$idKey])>0&&self::dbRecordExists($data)) {
                $where = $table->getAdapter()->quoteInto("$idKey = ?", $store[$idKey]);
                   return $table->update($store, $where);
            } else {
                return $table->insert($store);
            }
        } else {
            throw new \Exception($dataType."::save requires data of appropriate type");
        }
    }

    public static function delete($data) {
            $dataType = self::_getDataType();
            if($data instanceof $dataType) {
                $idKey = self::_getModelPrimaryIdKey();
                $store = array(
                    $idKey   => $data->getId()
                );
                if(self::dbRecordExists($data)) {
                    $tblNm = self::_getDbTableName(get_called_class());
                    $table = self::_getDbTableGateway($tblNm);
                    $where = $table->getAdapter()->quoteInto("$idKey = ?", $data->getId());
                    return $table->delete($where);
                } else {
                    throw new \Exception($dataType."::delete record does not exist");
                }
            } else {
                throw new \Exception($dataType."::delete requires data of appropriate type");
            }
    }
    /**
     * checks to see if records for a given id exist in the database
     * @param object  $data
     * @param string  classname for type of #data
     * @return boolean  returns true if record of id exists
     */
    public static function dbRecordExists($data) {
        $dataType = self::_getDataType();

        if($data instanceof $dataType) {
            $idKey = self::_getModelPrimaryIdKey();
            if(intval($data->$idKey) > 0) {
                $model = self::getModelById($data->getId());
                return (boolean) ($model instanceof $dataType);
            } else {
                throw new \Exception(__METHOD__." id not set");
            }
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    /**
     * Retrieve a model by it's primary id
     * @param integer $id
     * @throws Exception if not found
     * @return __CLASS__
     */
    public static function getModelById($id) {
        $dataType = self::_getDataType();
        $dataMapper = get_called_class();
        $id = intval($id);
        $idKey = self::_getModelPrimaryIdKey();
        if($id>0) {
            $tblNm = self::_getDbTableName(get_called_class());
            $table = self::_getDbTableGateway($tblNm);
            $adptr = $table->getAdapter();
            $where = $adptr->quoteInto($idKey.' = ?', $id);
            $rs = $table->fetchAll(
                $table->select()
                    ->where($where)
            );
            if(count($rs)==1) {
                $row = $rs->current();
                $new =  self::_createModelFromRow($row);
                return $new;
            } else {
                return false;
                //throw new \Exception(__METHOD__." row not found for id = $id");
            }
        } else {
            throw new \Exception(__METHOD__." id '$id' must be positive integer");
        }
    }

    public static function getModelJoined($id=null) {
        $dataMapper = get_called_class();
        $dataType   = self::_getDataType();
        $idKey      = self::_getModelPrimaryIdKey();

        $dB = self::_getDbTableGateway()->getAdapter();
        $select = $dB->select();

        $select->from(
            array('p' => $dataMapper::_getDbTableName()),
            array_merge(
                array(
                    'id'          => $idKey,
                    'name'        => $dataType::_getLabelName(),
                    'description' => "description"
                ),
                $dataType::_getDataMap()
            )
        );

        if(isset($dataType::$_subs)) {
            foreach($dataType::$_subs as $subAlias =>$subMapper) {
                $subDataType = $subMapper::_getDataType();
                $subTable    = $subMapper::_getDbTableName();
                $subidKey    = $subMapper::_getModelPrimaryIdKey();
                $select->joinInner(
                    array($subAlias => $subTable),
                    sprintf("%s.%s = p.%s", $subAlias, $subDataType::_getPrimaryIdKey(), $subDataType::_getPrimaryIdKey() ),
                    array_merge(
                        array(
                            $dataType::_getDataType() . ucfirst($subidKey) => $subidKey,
                            $dataType::_getDataType() . ucfirst($subDataType::_getDataType()) . 'Name'        => "name",
                            $dataType::_getDataType() . ucfirst($subDataType::_getDataType()) . 'Description' => "description"
                        ),
                        $subDataType::$_dataMap
                    )
                );
            }
        }

        if(isset($id)&&(intval($id) > 0)) {
            $select->where("p.$idKey = ?", $id);
        }
        $stmt = $dB->query($select);
        $res = $stmt->fetchAll();

        return $res;
    }

    public static function getIdsAsArray() {
        $dataMapper = get_called_class();

        $dataType = self::_getDataType();
        $idKey = self::_getModelPrimaryIdKey();
        $all = self::getAll($idKey);

        $ids = array();
        foreach($all as $row) {
            $ids[$row->$idKey] = $row->$label;
        }
        return $ids;
    }

    public static function getIdLimits($id=null) {
        $dataMapper = get_called_class();

        $tblNm    = self::_getDbTableName();
        $table    = self::_getDbTableGateway($tblNm);
        $dataType = self::_getDataType();
        $idKey    = self::_getModelPrimaryIdKey();

        $limits = array();
        $rs = $table->fetchAll(
            $table->select($idKey)
                ->order("$idKey ASC")
                ->limit(1,0)
        );
        if(count($rs)>0) {
            $limits['min'] = $rs->current()->$idKey;
        }

        // if min is still null, there are no rows in table
        if(isset($limits['min'])) {
            $rs = $table->fetchAll(
                $table->select($idKey)
                    ->order("$idKey DESC")
                    ->limit(1,0)
            );
            if(count($rs)>0) {
                $limits['max'] = $rs->current()->$idKey;
            }
        }

        // if we had an id given, try to find next/prev
        if(isset($id)) {
            // if min=max, there was one or fewer rows
            if(    isset($limits['min']) && isset($limits['max']) && ($limits['min'] !== $limits['max']) ) {
                $rs = $table->fetchAll(
                    $table->select($idKey)
                        ->where(sprintf("%s > ?", $idKey), $id)
                        ->order("$idKey ASC")
                        ->limit(1,0)
                );
                if(count($rs)>0) {
                    $limits['next'] = $rs->current()->$idKey;
                } else {
                    // use minimum id if there were none higher
                    $limits['next'] = $limits['min'];
                }
                $rs = $table->fetchAll(
                    $table->select($idKey)
                        ->where(sprintf("%s < ?", $idKey), $id)
                        ->order("$idKey DESC")
                        ->limit(1,0)
                );
                if(count($rs)>0) {
                    $limits['prev'] = $rs->current()->$idKey;
                } else {
                    // use maximum id if there were none higher
                    $limits['prev'] = $limits['max'];
                }
            }
        }
        return $limits;
    }

    /**
     * get all values as array of models
     * @return array   of models
     * @throws \Exception if unable to retrieve records
     */
    public static function getAll() {
        $dataMapper = get_called_class();
        $dataType = self::_getDataType();
        $tblNm    = self::_getDbTableName();
        $table    = self::_getDbTableGateway($tblNm);

        if($table instanceof \Zend\Db\Table) {
            $rs = $table->fetchAll(
                $table->select()
            );
            $models = array();
            foreach($rs as $row) {
                array_push($models, self::_createModelFromRow($row));
            }
            return $models;
        } else {
            throw new \Exception(__METHOD__." unable to retrieve '$dataMapper' table");
        }
    }

    /**
     * (should be overridden in child to pass appropriate id and classname)
     * @param \Zend\Db\Table_Row $row
     * @return object of type $classname
     */
    public static function _createModelFromRow(\Zend\Db\Table_Row $row) {
        $dataType = self::_getDataType();
        $data = $row->toArray();
        $newModel = new $dataType($data);
        return $newModel;
    }

    public static function _getDataType() {
        $dataMapper = get_called_class();
        if(defined($dataMapper."::MODEL_NAME")) {
            return $dataMapper::MODEL_NAME;
        } else {
            // try to guess the model name from the called class name
            $_dt = preg_replace('/Mapper/', '', get_called_class());
            if(class_exists($_dt)) {
                return $_dt;
            } else {
                throw new \Exception(__METHOD__." Unable to determine datatype, please specify");
            }
        }
    }

    public static function _getModelPrimaryIdKey() {
        $dataType = self::_getDataType();
        return $dataType::_getPrimaryIdKey();
    }

    public static function _getModelLabelName() {
        $dataType = self::_getDataType();
        return $dataType::_getLabelName();
    }

    public static function _getDbTableName() {
        $dataMapper = get_called_class();
        if(defined($dataMapper.'::DB_TABLE_NAME')) {
            return $dataMapper::DB_TABLE_NAME;
        } else {
            throw new \Exception($dataMapper." DB_TABLE_NAME not defined");
        }
    }

    /**
     * check if an inherited mapper is set up properly
     * @static
     * @throws \Exception if invalid
     */
    private static function _childIsValid($testModel=false) {
        $dataMapper = get_called_class();
        // run local get methods to make sure MODEL_NAME and DB_TABLE_NAME are set properly in child
        $dataType = self::_getDataType();

        if($testModel) {
            //$dataType::_childIsValid();
            if(class_exists($dataType)) {
                $dataType::_getPrimaryIdKey();
                $dataType::_getLabelName();
                $dataType::_getDataTypeName();
            }
        }

        // make sure child has
        if(defined($dataMapper . '::DB_TABLE_NAME')) {
            // see if we can get the table (also caches it)
           $dbTable = $dataMapper::_getDbTable();
            if(!($dbTable instanceof \Zend\Db\Table)) {
                throw new \Exception(__METHOD__." unable to retrieve table: '" . $dataMapper::DB_TABLE_NAME . "'" );
            }
        } else {
            throw new \Exception(__METHOD__." called class '$dataMapper' has no defined DB_TABLE_NAME");
        }
    }
}

