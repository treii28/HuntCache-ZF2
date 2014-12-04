<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 12:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Mapper;

class UserMapper extends MapperAbstract
{
    const DB_TABLE_NAME = 'users';
    const MODEL_NAME    = 'User';
    protected static $_dbTable = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * return true or false if a user exists in the database
     * @static
     * @throws \Exception
     * @param Application\Model\User $data
     * @return bool
     */
    public static function userExists(Application\Model\User $data) {
        $model = self::_getDataType();
        $idname = $model::_getPrimaryIdKey();
        $method = "get".ucfirst($idname);
        $pId = $data->$method();
        if($pId > 0) {
            return parent::dbRecordExists($data);
        } elseif((string) $data->getName() !== '') {
            $row = self::getAttributeByName($data->getName());
            return (boolean) ($row instanceof Zend_Db_Table_Row);
        } else {
            throw new \Exception(__METHOD__." requires valid/populated model as input");
        }
    }

    /**
     * retrieve a user by their username
     * @static
     * @param string $name
     * @return bool|Application\Model\User
     */
    public static function getUserByUsername($name) {
        $name = (string) $name;
        $table = self::_getDbTable();
        $where = $table->getAdapter()->quoteInto('username = ?', $name);
        if($name !== '') {
            $rs = $table->fetchAll(
                $table->select()
                    ->where($where)
            );

            if(count($rs)==1) {
                $row = $rs->current();
                return self::_createModelFromRow($row);
            } else {
                return false;
                //throw new \Exception(__METHOD__." row not found for username = '$name'");
            }
        }
    }

    /**
     * Retrieve an user by primary id
     * @param integer $id
     * @throws Exception if not found
     * @return Application\Model\Attribute
     */
    public static function getUserById($id) {
        $model = self::_getDataType();
        return parent::getModelById($id);
    }

    public static function getUserExtById($id) {
        $id    = intval($id);
        $model = self::_getDataType();
        $pId   = $model::_getPrimaryIdKey();
        if($id > 0) {
            $table = self::_getDbTable();
            $where = $table->getAdapter()->quoteInto($pId.' = ?',$id);
            $row = $table->fetchRow(
                $table->select()
                    ->where($where)
            );
        }
    }

    public static function getUsernameList() {
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

    public static function getUsernames() {
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

    public static function getUserDetailById($id)
    {
        $id = intval($id);
    }
}