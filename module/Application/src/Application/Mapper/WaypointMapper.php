<?php

namespace Application\Mapper;

class WaypointMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'waypoints';
    const MODEL_NAME	= 'Application\Model\Waypoint';
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Store waypoint data into database
     * @param Application\Model\Waypoint $wpt
     * @throws \Exception   if id is set but not a positive integer
     * @return int  rows effected
     */
    public static function save($wpt) {
        $dataType = self::_getDataType();
        if($wpt instanceof $dataType) {
            $data = $wpt->getData();
            $data['waypointId'] = $wpt->getWaypointId();

            if(isset($data['waypointId'])) {
                if($data['waypointId']>0) {
                    if(self::waypointExists($data['srcid'])) {
                        // update record
                        return self::_getDbTable()->update(
                            $data,
                            array('waypointId = ?' => $data['waypointId'])
                        );
                    }
                } else {
                    throw new \Exception(__METHOD__." waypointId must be a positive integer: ".$data['waypointId']);
                }
            } else {
                // insert a new record
                return self::_getDbTable()->insert($data);
            }
        } else {
            throw new \Exception(__METHOD__." data must be of type '$dataType'");
        }
    }

    /**
     * Attempt to retrieve waypoint data from the database
     *   populate a new Waypoint model instance from row if found
     * @param int $id
     * @return Application\Model\Waypoint|null
     */
    public static function getWaypointById($id) {
        $id = intval($id);
        $newWaypoint = null;

        $row = self::_getDbTable()->fetchRow(
            self::_getDbTable()->select()
                    ->where('waypointId = ?', $id)
        );
        if(count($row)>0) {
            $dataType = self::_getDataType();
            $newWaypoint = new Application\Model\Waypoint();
            $newWaypoint->setWaypointId($row->waypointId);
            foreach($dataType::_getDataFieldNames() as $_f) {
                if($row->$_f !== null) {
                    $method = "set".ucfirst($_f);
                    $foo = $row->$_f;
                    $newWaypoint->$method($row->$_f);
                }
            }
        }
        return $newWaypoint;
    }

    /**
     * checks to see if records for a given id exist in the database
     * @param int $id
     * @return boolean  returns true if record of id exists
     */
    public static function waypointExists(Application\Model\Waypoint $data) {
        $model = self::_getDataType();
        $idName = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idName);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $rec = self::getWaypointByName($data->getName());
            return (boolean) ($rec instanceof $model);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    public static function getWaypointByName($name) {
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
     * retrieve all attributes as array of attribute names
     * @return array
     */
    public static function getWaypointList() {
        $model = self::_getDataType();
        $LblNm   = $model::_getLabelName();
        $rs = self::_getDbTable()-fetchAll(
            self::_getDbTable()->select($LblNm)
        );
        $labels = array();
        foreach($rs as $row) {
            array_push($labels, $row->$LblNm);
        }
        return $labels;
    }
}

