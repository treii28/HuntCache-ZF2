<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 10:34 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

use \Application\Model\Sub\AccountTypeInterface;
use \Application\Model\Sub\StateInterface;
use \Application\Model\Sub\CountryInterface;
use \Application\Model\Sub\TitleInterface;

class User extends ModelAbstract implements AccountTypeInterface,StateInterface,CountryInterface,TitleInterface
{
    const PRIMARY_ID_KEY  = 'userId';
    const LABEL_FIELD_NAME = 'username';
    const DATA_TYPE_NAME   = 'user';
    protected static $_dataMapperName = 'UserMapper';

    private $role = null;

    protected static $_dataStruct = array(
        'accountTypeId' => null,        'titleId'       => null,
        'username'      => null,        'password'      => null,
        'firstName'     => null,        'lastName'      => null,
        'address1'      => null,        'address2'      => null,
        'city'          => null,        'stateId'       => null,
        'zip'           => null,        'countryId'     => null,
        'email'         => null,        'company'       => null,
        'phone'         => null,        'mobile'        => null,
        'website'       => null,        'description'   => null,
        'notes'         => null
    );

    protected static $_subs = array(
        'accountType' => "Application\Model\AccountTypeMapper",
        'title'       => "Application\Model\TitleMapper",
        'state'       => "Application\Model\StateMapper",
        'country'     => "Application\Model\CountryMapper"
    );

    protected static $_dataMap = array(
        'username'    => "username", // exclude?
        'password'    => "password",
        'firstName'   => "firstName",
        'lastName'    => "lastName",
        'address1'    => "address1",
        'address2'    => "address2",
        'city'        => "city",
        'zip'         => "zip",     // postalCode
        'email'       => "email",   // emailAddress
        'company'     => "company", // companyName
        'phone'       => "phone",   // phoneNumber
        'mobile'      => "mobile",  // mobileNumber
        'website'     => "website"  // url
    );

    public  $userId;
    public  $accountType;
    private $title;
    private $state;
    private $country;

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    public function setAccountType($data=null) {
        $this->accountType = Application\Model\AccountTypeMapper::autoDefine($data);
        $this->_data['accountTypeId'] = $this->accountType->getId();
        return $this->getAccountType();
    }

    public function getAccountType() {
        if(!($this->accountType instanceof Application\Model\AccountType) && (isset($this->_data['accountTypeId']) && (intval($this->_data['accountTypeId']) > 0))) {
            $this->setAccountType(intval($this->_data['accountTypeId']));
        }
        if($this->accountType instanceof Application\Model\AccountType) {
            return $this->accountType;
        } else {
            return false;
        }
    }

    public function setAccountTypeById($id=null) {
        $this->accountType = Application\Model\AccountTypeMapper::getAccountTypeById($id);
        $this->_data['accountTypeId'] = $this->accountType->getId();
        return $this->getAccountTypeId();
    }

    public function getAccountTypeId() {
        if($this->accountType instanceof Application\Model\AccountType) {
            return $this->accountType->getId();
        } elseif(isset($this->_data['accountTypeId'])) {
            return intval($this->_data['accountTypeId']);
        } else {
            return false;
        }
    }

    public function setAccountTypeByName($name) {
        $this->accountType = Application\Model\AccountTypeMapper::getAccountTypeByName($name);
        $this->_data['accountTypeId'] = $this->accountType->getId();
        return $this->getAccountTypeName();
    }
    public function getAccountTypeName() {
        if($this->accountType instanceof Application\Model\AccountType) {
            return $this->accountType->getName();
        } else {
            return false;
        }
    }

    public function setTitle($data) {
        $this->title = Application\Model\TitleMapper::autoDefine($data);
        $this->_data['titleId'] = $this->title->getId();
        return $this->getTitle();
    }

    public function getTitle() {
        if(!($this->title instanceof Application\Model\Title) && (isset($this->_data['titleId']) && (intval($this->_data['titleId']) > 0))) {
            $this->settitle(intval($this->_data['titleId']));
        }
        if($this->title instanceof Application\Model\Title) {
            return $this->title;
        } else {
            return false;
        }
    }

    public function setTitleById($id) {
        $this->title = Application\Model\TitleMapper::getTitleById($id);
        $this->_data['titleId'] = $this->title->getId();
        return $this->getTitleId();
    }

    public function getTitleId() {
        if($this->title instanceof Application\Model\Title) {
            return $this->title->getId();
        } elseif(isset($this->_data['titleId'])) {
            return intval($this->_data['titleId']);
        } else {
            return false;
        }
    }

    public function setTitleByName($name) {
        $this->title = Application\Model\TitleMapper::getTitleByName($name);
        $this->_data['titleId'] = $this->title->getId();
        return $this->getTitleName();
    }
    public function getTitleName() {
        if($this->title instanceof Application\Model\Title) {
            return $this->title->getName();
        } else {
            return false;
        }
    }

    public function setCountry($data=null) {
        $this->country = Application\Model\CountryMapper::autoDefine($data);
        $this->_data['countryId'] = $this->country->getId();
        return $this->country;
    }

    public function getCountry() {
        if(!($this->country instanceof Application\Model\Country) && (isset($this->_data['countryId']) && (intval($this->_data['countryId']) > 0))) {
            $this->setCountry(intval($this->_data['countryId']));
        }
        if($this->country instanceof Application\Model\Country) {
            return $this->country;
        } else {
            return false;
        }
    }

    public function setCountryById($id=null) {
        $this->country = Application\Model\CountryMapper::getCountryById($id);
        $this->_data['countryId'] = $this->country->getId();
        return $this->getCountryId();
    }

    public function getCountryId() {
        if($this->country instanceof Application\Model\Country) {
            return $this->country->getId();
        } elseif(isset($this->_data['countryId'])) {
            return $this->country->_data['countryId'];
        } else {
            return false;
        }
    }

    public function setCountryByName($name) {
        $this->country = Application\Model\CountryMapper::getCountryByName($name);
        $this->_data['countryId'] = $this->country->getId();
        return $this->getCountryName();
    }
    public function getCountryName() {
        if($this->country instanceof Application\Model\Country) {
            return $this->country->getName();
        } else {
            return false;
        }
    }

    public function setState($data) {
        $this->state = Application\Model\StateMapper::autoDefine($data);
        $this->_data['stateId'] = $this->state->getId();
        return $this->getState();
    }

    public function getState() {
        if(!($this->state instanceof Application\Model\State) && (isset($this->_data['stateId']) && (intval($this->_data['stateId']) > 0))) {
            $this->setState(intval($this->_data['stateId']));
        }
        if($this->state instanceof Application\Model\State) {
            return $this->state;
        } else {
            return false;
        }
    }

    public function setStateById($id) {
        $this->state = Application\Model\StateMapper::getStateById($id);
        $this->_data['stateId'] = $this->state->getId();
        return $this->getStateId();
    }
    public function getStateId() {
        if($this->state instanceof Application\Model\State) {
            return $this->state->getId();
        } elseif(isset($this->_data['stateId'])) {
            return $this->_data['stateId'];
        }
    }

    public function setStateByName($name) {
        $this->state = Application\Model\StateMapper::getStateByName($name);
        return $this->getStateName();
    }
    public function getStateName() {
        if($this->state instanceof Application\Model\State) {
            return $this->state->getName();
        } else {
            return false;
        }
    }

    // alias methods
    public function getPostalCode() {
        return $this->getZip();
    }
    public function setPostalCode($str) {
        return $this->setZip($str);
    }
}