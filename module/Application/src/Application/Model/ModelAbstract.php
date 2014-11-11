<?php
namespace Application\Model;

abstract class ModelAbstract
{
    const PRIMARY_ID_KEY  = 'Id';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = '';

    protected static $_dataMapperName = '';
    protected static $_dataMapper     = null;

    public $_options;
    /**
     * main protected array of model data properties
     * @var array
     */
    protected $_data = array();
    protected static $_dataStruct = array(
        'name' => null,
        'description' => null
    );

    protected static $_dataMap = array();
    protected static $_subs    = null;

    /**
     * create a 'new' instance of the class
     *   calls _init() if provided an array of data
     * @param array $data   optional data consisting of key/value pairs
     */
    public function __construct ($data=null)
    {
        //$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        //$this->_options = $bootstrap->getOptions();
        $this->_init($data);
    }

    /**
     * Initialize an instance of the class with an array of key/value pairs
     * @param array $data
     * @throws \Exception
     */
    public function _init($data) {
        if(is_array($data)) {
            foreach($data as $_k => $_v) {
                $this->__set($_k,$_v);
            }
        } elseif(is_integer($data)) { // assume Id and try to get from db with mapper
            $dataType   = get_called_class();
            $dataType   = $dataType::DATA_TYPE_NAME;
            $dataMapper = self::_getMapperName();
            $newObj = $dataMapper::getModelById($data,$dataType);
            if($newObj instanceof $dataType) {
                // clone newObj into this
                $newRef = new \ReflectionClass($newObj);
                foreach($newRef->getProperties() as $_prop) {
                    if(!$_prop->isStatic()) {
                        $_propName = $_prop->name;
                        $this->$_propName = $newObj->$_propName;
                    }
                }
            } else {
                throw new \Exception(__METHOD__." Unable to initialize from Id '$data'");
            }
        }
    }

    /**
     * Wrapper for magic methods
     * @param string $methodName
     * @param unknown_type $data
     * @throws \Exception for unknown method
     */
    public function __call($methodName, $data=null) {
        preg_match('/^([gs]et)(\w+)$/', $methodName, $matches);
        $property = lcfirst($matches[2]);
        //$method = preg_replace('/ies$/', 'y', $method);
        //$method = preg_replace('/s$/', '', $method);

        // only first parameter is used on magic methods
        switch($matches[1]) {
            case "set":
                return $this->__set($property,current($data));
            case "get":
                return $this->__get($property);
            default:
                throw new \Exception(__METHOD__," unknown method '$methodName'");
        }
    }

    /**
     * Magic method for setting properties in the _data associative array
     * @param string $property key name of property
     * @param mixed $data  value of property
     * @throws \Exception triggers error if property does not exist in _data
     */
    public function __set($property,$data)
    {
        $setMthd = 'set'.ucfirst($property);
        $dataType  = get_called_class();
        if(in_array($setMthd,$this->_getAllMethods())) {
            $this->$setMthd($data);
            return $this->$property;
        } elseif(in_array($property,$this->_getAllProperties())) {
            // check for an create/initialize sub objects
            if(isset($dataType::$_subs)&&array_key_exists($property,$dataType::$_subs)) {
                $subClass = $dataType::$_subs[$property];
                if($this->$property instanceof $subClass) {
                    $this->$property->_init($data); // sub classes must have a public _init
                } else {
                    $this->$property = new $subClass($data);
                }
            } else {
                $this->$property = $data;
            }
            return $this->$property;
        } elseif(array_key_exists($property,$this->_data)) {
            $this->_data[$property] = $data;
            return $this->_data[$property];
        } else {
            // this may be overkill but handle setting sub-object properties by
            // prefixing (sub) property name with sub datatype
            $notSub = true;
            if(in_array('_subs',$dataType::_getStaticPublicProperties())) {
                foreach(array_keys($dataType::$_subs) as $_sKey) {
                    $preg = '/^'.$_sKey.'(.*)$/';
                    if(preg_match($preg,$property,$matches)) {
                        $notSub = false;
                        $subProp = lcfirst($matches[1]);
                        $subClass = $dataType::$_subs[$_sKey];
                        if($this->$_sKey instanceof $subClass) {
                            // hand off to __set in sub object
                            $this->$_sKey->$property = $data;
                        } else {
                            // initialize sub with property
                            $this->$_sKey = new $subClass(array($subProp => $data));
                        }
                    }
                }
            }
            if($notSub) {
                throw new \Exception(__METHOD__." no such property: '$property'");
            }
        }
    }

    /**
     * Magic method accessor for getting properties from the _data array
     * @param string $property   key name of property
     * @throws Exception   triggers error if property does not exist in _data
     */
    public function __get($property)
    {
        if(array_key_exists($property,$this->_data)) {
            return $this->_data[$property];
        } elseif(in_array($property,$this->_getAllProperties())) {
            return $this->$property;
        } else {
            throw new \Exception(__METHOD__." no such property: $property");
        }
    }

    /**
     * Clear all of the values in the _data array and other properties
     */
    function free()
    {
        foreach ($this->_data as $field => $value) {
            $this->_data[$field] = null;
        }
        foreach (get_class_vars($this) as $property) {
            if($property!=='_data') {
                $this->$property = null;
            }
        }
    }

    /**
     * look at calling class to determine the primary key column name
     * then retrieve that value via the appropriate (auto) method
     * @return integer
     */
    public function getId() {
        $dataType = get_called_class();
        $pIdName = $dataType::_getPrimaryIdKey();
        $method  = "get".ucfirst($pIdName);
        return $this->$method();
    }

    /**
     * look at calling class to determine the primary key column name
     * then populate that value with the appropriate (auto) method
     * @param int $id
     * @return int|null
     */
    public function setId($id) {
        $dataType = get_called_class();
        $pIdName = $dataType::_getPrimaryIdKey();
        $this->$pIdName = intval($id);
        return $this->$pIdName;
    }

    /**
     * get the contents of the _data private variable (associative array)
     * @return array
     * @throws \Exception for no _data in child
     */
    public function getData() {
        if(property_exists($this, '_data')) {
            return $this->_data;
        } else {
            throw new \Exception(get_called_class()." has no '_data' property set");
        }
    }

    /**
     * return model data as an associative array
     * @return array
     */
    public function asArray() {
        $mArr = array(
            $this->getId() => $this->getData()
        );
        return $mArr;
    }

    /**
     * Retrieve just the key names from the private data array
     * @static
     * @return array
     * @throws \Exception
     */
    public static function _getDataFieldNames() {
        $dataType = get_called_class();
        if(is_array($dataType::$_dataStruct)) {
            return array_keys($dataType::$_dataStruct);
        } else {
            throw new \Exception($dataType." appears to have no \$_dataStruct array");
        }
    }

    /**
     * get the column name for the model's primary key
     * @static
     * @return string|null
     * @throws \Exception for improperly (non) defined value in child class
     */
    public static function _getPrimaryIdKey() {
        $dataType = get_called_class();
        if(defined($dataType.'::PRIMARY_ID_KEY') && ($dataType::PRIMARY_ID_KEY !== '')) {
            return $dataType::PRIMARY_ID_KEY;
        } else {
            throw new \Exception($dataType." has no defined PRIMARY_ID_KEY");
        }
    }

    /**
     * get the key name for the label field
     * @static
     * @return string
     * @throws \Exception
     */
    public static function _getLabelName() {
        $dataType = get_called_class();
        if(defined($dataType.'::LABEL_FIELD_NAME') && ($dataType::LABEL_FIELD_NAME !== '')) {
            return $dataType::LABEL_FIELD_NAME;
        } else {
            throw new \Exception($dataType." has no defined LABEL_FIELD_NAME");
        }
    }

    /**
     * check the calling class public static variables to get it's model (data) type name
     * @static
     * @return string
     * @throws \Exception for improperly (non) defined value in child class
     */
    public static function _getDataTypeName() {
        $dataType = get_called_class();
        if(defined($dataType.'::DATA_TYPE_NAME')) {
            if($dataType::DATA_TYPE_NAME !== '') {
                return $dataType::DATA_TYPE_NAME;
            } else {
                throw new \Exception($dataType." DATA_TYPE_NAME is not set");
            }
        } else {
            throw new \Exception($dataType." has no defined DATA_TYPE_NAME");
        }
    }

    /**
     * check the calling class public static variables to get it's mapper name
     * @static
     * @return string
     * @throws \Exception for improperly (non) defined value in child class
     */
    public static function _getMapperName() {
        $dataType = get_called_class();
        // try to find mapper name if not specified and store it
        if(!(defined($dataType.'::$_dataMapperName')) || ($dataType::$_dataMapperName === '')) {
            $default = $dataType."Mapper";
            if(class_exists($default)) {
                $dataType::$_dataMapperName = $default;
            }
        }

        if(class_exists($dataType::$_dataMapperName)) {
            return $dataType::$_dataMapperName;
        } else {
            throw new \Exception($dataType." unable to determine valid dataMapperName");
        }
    }

    /**
     * attempt to get a copy of this model's data mapper
     * @static
     * @return mixed
     */
    public static function _getDataMapper() {
        $dataType = get_called_class();
        $dataMapperName = self::_getMapperName();
        if(!($dataType::$_dataMapper instanceof $dataMapperName)) {
            $dataType::$_dataMapper = new $dataMapperName();
        }
        return $dataType::$_dataMapper;
    }

    public static function _getDataStruct() {
        $dataType = get_called_class();
        return $dataType::$_dataStruct;
    }
    public static function _getDataMap() {
        $dataType = get_called_class();
        return $dataType::$_dataMap;
    }
}
