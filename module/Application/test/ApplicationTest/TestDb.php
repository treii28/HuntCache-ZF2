<?php
/**
 * Created by PhpStorm.
 * User: scottw
 * Date: 2/13/14
 * Time: 3:28 PM
 */

namespace ApplicationTest;

use \Zend\Db\Adapter\Adapter;

class TestDb {

    const SOURCE_DB_NAME = 'huntcache';
    const TEST_DB_NAME = 'testing';

    private $_origDb;
    private $_db = array();

    protected $_dbDriver   = "Pdo_Mysql";
    protected $_dbUsername = "swood";
    protected $_dbPassword = "buffalo";

    public function __construct() {
        $this->_origDb = new \Zend\Db\Adapter\Adapter(
            array(
                'driver' => $this->_dbDriver,
                'database' => self::SOURCE_DB_NAME,
                'username' => $this->_dbUsername,
                'password' => $this->_dbPassword
            )
        );

        $this->createTestDb();
    }

    public function __destruct() {
        $this->destroyTestDb();
        // set default 'db' back in zend registry upon shutdown
    }

    /**
     * @param string|null $dbName
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getDb($dbName=null) {
        $dbName = ((string)$dbName != '') ? $dbName : self::TEST_DB_NAME;
        if(!(isset($this->_db[$dbName]))) {
            require_once("Zend/Db.php");
            $this->_db[$dbName] = new \Zend\Db\Adapter\Adapter(
                array(
                    'driver'   => $this->_dbDriver,
                    'database' => $dbName,
                    'username' => $this->_dbUsername,
                    'password' => $this->_dbPassword
                )
            );
        }
        return $this->_db[$dbName];
    }

    public function createTestDb() {
        // use a database other than the one we are creating
        //return $this->getDb('mysql')->query('CREATE DATABASE IF NOT EXISTS `testing`;');
        return $this->getDb('test')->query('CREATE DATABASE IF NOT EXISTS `testing`;');
    }

    public function destroyTestDb() {
        // use a database other than the one we are destroying
        //return $this->getDb('mysql')->query('DROP DATABASE IF EXISTS `testing`;');
        return $this->getDb('test')->query('DROP DATABASE IF EXISTS `testing`;');
    }

    public function resetTestDb() {
        $this->destroyTestDb();
        $this->createTestDb();
    }

    public function createTableClone($tableName) {
        $destDb   = self::TEST_DB_NAME;
        $sourceDb = self::SOURCE_DB_NAME;
        return $this->getDb()->query(sprintf("CREATE TABLE IF NOT EXISTS `%s`.`%s` LIKE `%s`.`%s`;", $destDb, $tableName, $sourceDb, $tableName));
    }

    public function cloneTableRecord($id, $tableName) {
        require_once("Zend/Db/Table.php");
        $destDb = $this->getDb(self::TEST_DB_NAME);
        \Zend_Db_Table::setDefaultAdapter($destDb);
        $destDbTable = new \Zend_Db_Table($tableName);
        $sourceDb = $this->getDb(self::SOURCE_DB_NAME);
        \Zend_Db_Table::setDefaultAdapter($sourceDb);
        $sourceDbTable = new \Zend_Db_Table($tableName);

        $rows = $sourceDbTable->find($id);
        $row = $rows->current()->toArray();
        return $destDbTable->insert($row);
    }

    public function cloneAllTableData($tableName) {
        require_once("Zend/Db/Table.php");
        $destDb = $this->getDb(self::TEST_DB_NAME);
        \Zend_Db_Table::setDefaultAdapter($destDb);
        $destDbTable = new \Zend_Db_Table($tableName);
        $sourceDb = $this->getDb(self::SOURCE_DB_NAME);
        //\Zend_Db_Table::setDefaultAdapter($sourceDb);
        //$sourceDbTable = new \Zend_Db_Table($tableName);

        $stmt = $sourceDb->query("SELECT * FROM `" . $tableName . "`");
        while($row = $stmt->fetch()) {
            $destDbTable->insert($row);
        }
    }

    /**
     * get \Zend_Db_Table object for a given table in the default (test) database
     *
     * @param string $tableName
     * @return \Zend_Db_Table
     */
    public function getMockTable($tableName) {
        $this->createTableClone($tableName);
        require_once("Zend/Db/Table.php");
        \Zend_Db_Table::setDefaultAdapter($this->getDb());
        return new \Zend_Db_Table($tableName);
    }

    /**
     * insert an associative array of data into the default (test) database
     *
     * @param string $tableName
     * @param array $data
     */
    public function insertMockData($tableName, array $data) {
        $this->getDb()->insert($tableName, $data);
    }
}
 