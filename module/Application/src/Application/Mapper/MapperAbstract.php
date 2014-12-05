<?php

namespace Application\Mapper;

use \Zend\Db\Adapter\Adapter;
use \Zend\Db\TableGateway\TableGateway;
use \Zend\Db\RowGateway\RowGateway;
use \Zend\ServiceManager\ServiceLocatorAwareInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;

abstract class MapperAbstract implements ServiceLocatorAwareInterface
{
    protected $dbAdapter;
    protected $dbTable;
    const DB_TABLE_NAME = '';

    // <editor-fold desc="ServiceLocatorAwareInterface methods">
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service_manager = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->service_manager;
    }
    // </editor-fold desc="ServiceLocatorAwareInterface methods">

    // <editor-fold desc="TableGateway methods">
    public function getDbAdapter()
    {
        if (!$this->dbAdapter) {
            $this->adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        }
        return $this->dbAdapter;
    }

    /**
     * @return \Zend\Db\TableGateway\TableGateway
     * @throws \Exception
     */
    public function getDbTableGateway()
    {
        $tableName = self::_getDbTableName();
        if(!isset($this->dbTable)) {
            $this->dbTable = new TableGateway($tableName,$this->getDbAdapter());
        }
        if($this->dbTable instanceof TableGateway) {
            return $this->dbTable;
        } else {
            throw new \Exception(__METHOD__." unable to retrieve db table '$tableName''");
        }
    }

    // </editor-fold desc="TableGateway methods">

    /**
     * abstract class constructor
     */
    public function __construct() {
        $this->_childIsValid();
        parent::__construct();
    }

    public function save($data) {
        $dataType = $this->_getDataType();

        if($data instanceof $dataType) {
            $idKey = $this->_getModelPrimaryIdKey();
            $store = array(
                $idKey   => $data->getId()
            );
            foreach($data->getData() as $k => $v) {
                $store[$k] = $v;
            }

            $table = $this->getDbTableGateway();
            if(intval( ($store[$idKey]) > 0) && ($this->dbRecordExists($data)) ) {
                $where = $this->getDbAdapter()->quoteInto("$idKey = ?", $store[$idKey]);
                   return $table->update($store, $where);
            } else {
                return $table->insert($store);
            }
        } else {
            throw new \Exception($dataType."::save requires data of appropriate type");
        }
    }

    public function delete($data) {
            $dataType = $this->_getDataType();
            if($data instanceof $dataType) {
                $idKey = $this->_getModelPrimaryIdKey();
                if($this->dbRecordExists($data)) {
                    $table = $this->getDbTableGateway();
                    $where = $this->getDbAdapter()->quoteInto("$idKey = ?", $data->getId());
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
     * @throws \Exception
     * @return boolean  returns true if record of id exists
     */
    public function dbRecordExists($data) {
        $dataType = self::_getDataType();

        if($data instanceof $dataType) {
            $idKey = $this->_getModelPrimaryIdKey();
            if(intval($data->$idKey) > 0) {
                $model = $this->getModelById($data->getId());
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
     * @return false|__CLASS__
     */
    public function getModelById($id) {
        $dataType = $this->_getDataType();
        $dataMapper = get_called_class();
        $id = intval($id);
        $idKey = $this->_getModelPrimaryIdKey();
        if($id>0) {
            $tblNm = $this->_getDbTableName(get_called_class());
            $table = $this->getDbTableGateway($tblNm);
            $adptr = $table->getAdapter();
            $where = $adptr->quoteInto($idKey.' = ?', $id);
            $rs = $table->select($where);
            if(count($rs)==1) {
                $row = $rs->current();
                $new =  $this->_createModelFromRow($row);
                return $new;
            } else {
                return false;
                //throw new \Exception(__METHOD__." row not found for id = $id");
            }
        } else {
            throw new \Exception(__METHOD__." id '$id' must be positive integer");
        }
    }

    public function getModelJoined($id=null) {
        $dataMapper = get_called_class();
        $dataType   = $this->_getDataType();
        $idKey      = $this->_getModelPrimaryIdKey();

        $dB = $this->getDbTableGateway()->getAdapter();
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

    public function getIdsAsArray() {
        $dataMapper = get_called_class();

        $dataType = $this->_getDataType();
        $idKey = $this->_getModelPrimaryIdKey();
        $all = $this->getAll($idKey);

        $ids = array();
        foreach($all as $row) {
            $ids[$row->$idKey] = $row->$label;
        }
        return $ids;
    }

    public function getIdLimits($id=null) {
        $dataMapper = get_called_class();

        $tblNm    = $this->_getDbTableName();
        $table    = $this->getDbTableGateway($tblNm);
        $dataType = $this->_getDataType();
        $idKey    = $this->_getModelPrimaryIdKey();

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
    public function getAll() {
        $dataMapper = get_called_class();
        $tblNm    = self::_getDbTableName();
        $table    = $this->getDbTableGateway($tblNm);

        if($table instanceof TableGateway) {
            $rs = $table->fetchAll(
                $table->select()
            );
            $models = array();
            foreach($rs as $row) {
                array_push($models, $this->_createModelFromRow($row));
            }
            return $models;
        } else {
            throw new \Exception(__METHOD__." unable to retrieve '$dataMapper' table");
        }
    }

    /**
     * (should be overridden in child to pass appropriate id and classname)
     * @param \Zend\Db\RowGateway\RowGateway $row
     * @return object of type $classname
     */
    public function _createModelFromRow(RowGateway $row) {
        $dataType = self::_getDataType();
        $data = $row->toArray();
        $newModel = new $dataType($data);
        return $newModel;
    }

    public static function _getDataType() {
        if(defined(self::MODEL_NAME) && !empty(self::MODEL_NAME) && class_exists(self::MODEL_NAME)) {
            return self::MODEL_NAME;
        } else {
            // try to guess the model name from the called class name
            // todo manipulate namespace to change Mapper for Model
            $_dt = preg_replace('/Mapper$/', '', get_called_class());
            if(class_exists($_dt)) {
                return $_dt;
            } else {
                throw new \Exception(__METHOD__." Unable to determine datatype, please specify in model");
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
        if(defined(self::DB_TABLE_NAME) && !empty(self::DB_TABLE_NAME)) {
            return self::DB_TABLE_NAME;
        } else {
            throw new \Exception(get_called_class()." DB_TABLE_NAME not defined");
        }
    }

    /**
     * check if an inherited mapper is set up properly
     * @static
     * @throws \Exception if invalid
     */
    private function _childIsValid($testModel=false) {
        $dataMapper = get_called_class();
        // run local get methods to make sure MODEL_NAME and DB_TABLE_NAME are set properly in child
        $dataType = $this->_getDataType();

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
            if(!($dbTable instanceof TableGateway)) {
                throw new \Exception(__METHOD__." unable to retrieve table: '" . $dataMapper::DB_TABLE_NAME . "'" );
            }
        } else {
            throw new \Exception(__METHOD__." called class '$dataMapper' has no defined DB_TABLE_NAME");
        }
    }
}

