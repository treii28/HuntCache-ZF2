<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 12:11 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

class AccountType extends ModelAbstract
{
    const PRIMARY_ID_KEY  = 'accountTypeId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'accountType';
    protected static $_dataMapperName = 'AccountTypeMapper';

    public $accountTypeId;

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    public function setAccountTypeId($id) {
        if(intval($id) > 0) {
            $this->accountTypeId = intval($id);
        } else {
            throw new \Exception(__METHOD__." '$id' must be a positive integer");
        }
        return $this->getAccountTypeId();
    }

    public function getAccountTypeId() {
        return intval($this->accountTypeId);
    }
}