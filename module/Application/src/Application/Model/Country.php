<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/8/11
 * Time: 5:31 PM
 * To change this template use File | Settings | File Templates.
 */
class Application\Model\Country extends Application\Model\ModelAbstract
{
    const PRIMARY_ID_KEY    = 'countryId';
    const LABEL_FIELD_NAME   = 'name';
    const DATA_TYPE_NAME     = 'country';
    protected static $_dataMapperName = 'CountryMapper';

    public $countryId;

    protected static $_dataStruct = array(
        'name'           => null,       'description'    => null,
        'country_3_code' => null,       'country_2_code' => null,
        'lat'            => null,       'long'           => null,
        'fips'           => null
    );

    protected static $_dataMap = array(
        'country_3_code' => "country_3_code",
        'country_2_code' => "country_2_code",
        'countryLat'     => "lat",
        'countryLong'    => "long",
        'fips'           => "fips"
    );

    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    public function __set($property,$data) {
        if($property === 'id') {
            return $this->setId($data);
        } else {
            return parent::__set($property,$data);
        }
    }

    public function setCountryId($id) {
        if(intval($id) > 0) {
            $this->countryId = intval($id);
        } else {
            throw new \Exception(__METHOD__." '$id' must be a positive integer");
        }
        return $this->getCountryId();
    }

    public function getCountryId() {
        return intval($this->countryId);
    }
}
