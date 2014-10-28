<?php

namespace Application\Model;

class Attribute extends ModelAbstract implements Sub\AttributeTypeInterface
{
    const PRIMARY_ID_KEY  = 'attributeId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'attribute';

    const DEFAULT_ICON     = 'geocaching.gif';

    protected static $_dataMapperName = 'AttributeMapper';

    private static $icondir = 'attributes';
    /**
     * default OCF attribute names (from original library)<br />
     *   becoming deprecated to support adding more attributes
     * @var array
     */
    public static $VALID_ATTRIBUTE_NAMES =
            array(
                'wheelchair',		'camping',			'parking',
                'publictransport',	'picnictables',		'drinkingwater',
                'restrooms',		'telephone',		'stroller',
                'museum',			'restaurant',		'night',
                'winter',			'scenic',			'stealth',
                'rocks',			'hunting',			'danger',
                'plants',			'thorns',			'snakes',
                'ticks',			'mine',				'dogs',
                'bicycles',			'motorcycles',		'quads',
                'offroad',			'snowmobiles',		'campfires',
                'horses',			'fee',				'climbing',
                'boat',				'scuba',			'kids',
                'short',			'hike',				'climbing',
                'wading',			'swimming',			'247'
            );

    /**
     * @var int $attributeId
     */
    public $attributeId;
    /**
     * @var AttributeType $attributeType
     */
    public $attributeType;

    /**
     * @var Attribute_Icon $icon
     */
    public $icon;

    protected static $_subs = array(
        'attributeType' => "Application\Model\AttributeTypeMapper",
        'icon'          => "Application\Model\Attribute_Icon"
    );

    /**
     * @param null|array $data
     */
    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
        if(!(isset($this->attributeType))) {
            $this->setAttributeType(); // initialize attribute type from default
        }
        if(!(isset($this->icon))) {
            $this->setIcon();
        }
    }

    /**
     * @param $property
     * @param $data
     * @return void
     */
    public function __set($property,$data) {
        if($property === 'id') {
            return $this->setId($data);
        } elseif($property === 'attributeTypeId') {
            return $this->setAttributeType($data);
        } else {
            return parent::__set($property,$data);
        }
    }

    /**
     * get data array
     * @return array
     */
    public function getData() {
        $_d = $this->_data;
        if($this->attributeType instanceof AttributeType) {
            $_d['attributeTypeId'] = $this->attributeType->getAttributeTypeId();
        }
        return $_d;
    }

    public function setIcon($data=null) {
        if(is_string($data)) {
            $fn = $data;
            $data = array();
            if($fn !== '') {
                $data['filename'] = $fn;
            }
        } elseif(is_null($data)) {
            $data = array();
        } elseif(!(is_array($data) || ($data instanceof Image\Icon))) {
            throw new \Exception(__METHOD__." input should be either a filename or an array of key/data pairs!");
        }

        if(is_array($data)) {
            if(!(isset($data['filepath']))) {
                $data['filepath'] = Image\Icon::_getCleanedPath(self::$icondir);
            }
            if(!(isset($data['urlpath']))) {
                $data['urlpath']  = Image\Icon::IMGURL_PREFIX . self::$icondir . '/';
            }
            if(!(isset($data['filename']))) {
                $data['filename'] = self::DEFAULT_ICON;
            }
        }

        $this->icon = new Application\Model\Image\Icon($data,self::$icondir);
        return $this->getIcon();
    }

    /**
     * @param null|int|AttributeType $aType
     * @return AttributeType
     */
    public function setAttributeType($data=null) {
        if($data instanceof AttributeType) {
            $this->attributeType = $data;
        } else {
            $this->attributeType = AttributeTypeMapper::autoDefine($data);
        }
        $this->_data['attributeTypeId'] = $this->attributeType->getId();
        return $this->attributeType;
    }

    public function getAttributeType() {
        if(!($this->attributeType instanceof AttributeType) && (isset($this->_data['attributeTypeId']) && (intval($this->_data['attributeTypeId']) > 0))) {
            $this->setAttributeType(intval($this->_data['attributeTypeId']));
        }
        if($this->attributeType instanceof AttributeType) {
            return $this->attributeType;
        } else {
            return false;
        }
    }

    /**
     * set attributeId
     * @param null|int $id
     * @return int
     */
    public function setAttributeTypeById($id=null) {
        $this->attributeType = AttributeTypeMapper::getAttributeTypeById($id);
        $this->data['attributeTypeId'] = $this->attributeType->getId();
        return $this->getAttributeTypeId();
    }

    public function getAttributeTypeId() {
        if($this->attributeType instanceof AttributeType) {
            return $this->attributeType->getId();
        } elseif(isset($this->_data['attributeTypeId'])) {
            return intval($this->_data['attributeTypeId']);
        } else {
            return false;
        }
    }

    public function setAttributeTypeByName($name) {
        $this->attributeType = AttributeTypeMapper::getAttributeTypeByName($name);
        $this->_data['attributeTypeId'] = $this->attributeType->getId();
        return $this->getAttributeTypeName();
    }

    public function getAttributeTypeName() {
        if($this->attributeType instanceof AttributeType) {
            return $this->attributeType->getName();
        } else {
            return false;
        }
    }
}

