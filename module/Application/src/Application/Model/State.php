<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/8/11
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */
class Application\Model\State extends Application\Model\ModelAbstract implements Application\Model\Sub_CountryInterface
{
    const PRIMARY_ID_KEY  = 'stateId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'state';
    protected static $_dataMapperName = 'StateMapper';

    public $stateId;

    protected static $_dataStruct = array(
        'name'         => null,     'description'  => null,
        'state_3_code' => null,     'state_2_code' => null,
        'lat'          => null,     'long'         => null,
        'num_code'     => null,     'countryId'    => null
    );

    protected static $_subs = array(
        'country' => "Application\Model\CountryMapper"
    );

    protected static $_dataMap = array(
        'state_3_code' => "state_3_code",
        'state_2_code' => "state_2_code",
        'stateLat'     => "lat",
        'stateLong'    => "long",
        'num_code'     => "num_code",
        'countryId'    => "countryId"
    );

    private $country;

    /**
     * @param null|array $data
     */
    public function __construct($data=null,$country=null) {
        $this->_data = self::$_dataStruct;
        $this->country = new Application\Model\Country($country);
        parent::__construct($data);
    }


    /**
     * @param $property
     * @param $data
     * @return void
     */
    public function __set($property,$data) {
        if($property === 'id') {
            return $this->setId($data);
        } elseif($property === 'countryId') {
            return $this->setCountryById($data);
        } else {
            return parent::__set($property,$data);
        }
    }

    public function getData() {
        $_d = $this->_data;
        if($this->country instanceof Application\Model\Country) {
            $_d['countryId']   = $this->country->getId();
            $_d['countryName'] = $this->country->getName();
        }
        return $_d;
    }

    public function setStateId($id) {
        if(intval($id) > 0) {
            $this->stateId = intval($id);
        } else {
            throw new \Exception(__METHOD__." '$id' must be a positive integer");
        }
        return $this->getStateId();
    }

    public function getStateId() {
        return intval($this->stateId);
    }

    public function setCountry($data=null) {
        if($data instanceof Application\Model\Country) {
            $this->country = $data;
        } else {
            $this->country = Application\Model\CountryMapper::autoDefine($data);
        }
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
            return intval($this->_data['countryId']);
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
}
