<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

abstract class Application\Model\ImageAbstract extends Application\Model\AbstractClass
{
    const IMGDIR_SUFFIX = 'public/img/';
    const IMGURL_PREFIX = '/img/';

    public $filepath;
    public $urlpath;
    public $filename;

    public function __construct ($data=null) {
        parent::__construct();
        //$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        //$this->_options = $bootstrap->getOptions();
        if(is_array($data)) {
            $this->_init($data);
        }
    }

    public function _init($data) {
        if(!(is_array($data))) {
            throw new \Exception(__METHOD__." expects array of key/value pairs");
        }
        foreach($data as $_k => $_v) {
            $this->__set($_k,$_v);
        }
    }

    public static function _getCleanedPath($dir='',$suff=null) {
        if(!(is_String($dir))) {
            throw new \Exception(__METHOD__." dir must be a string!");
        }
        $imgSuff = (!(is_null($suff)) && is_string($suff) && ($suff !== '')) ? $suff : self::IMGDIR_SUFFIX;
        $dir = preg_replace("/\/$/",'',$dir) . "/";
        $imgDir = preg_replace('/(^.*\/)application$/', '$1' . $imgSuff . $dir, APPLICATION_PATH);
        return $imgDir;
    }

    public function setFilepath($p) {
        if(!(isset($p)) || $p === '') {
            throw new \Exception(__METHOD__.' filepath not specified!');
        }
        if(!(preg_match("/\/$/",$p))) {
            $p .= '/';
        }
        $this->filepath = $p;
        return $this->getFilepath();
    }

    public function setUrlpath($p) {
        if(!(isset($p)) || $p === '') {
            throw new \Exception(__METHOD__.' urlpath not specified!');
        }
        if(!(preg_match("/\/$/",$p))) {
            $p .= '/';
        }
        $this->urlpath = $p;
        return $this->getUrlpath();
    }

    public function getAbsolutePath() {
        return $this->filepath.$this->filename;
    }

    public function getAbsoluteUrl() {
        return $this->urlpath.$this->filename;
    }
    /**
     * Wrapper for magic methods
     * @param string $methodname
     * @param unknown_type $data
     */
    public function __call($methodname, $data=null) {
        preg_match('/^([gs]et)(\w+)$/', $methodname, $matches);
        $magic  = "__".$matches[1];
        $method = lcfirst($matches[2]);
        //$method = preg_replace('/ies$/', 'y', $method);
        //$method = preg_replace('/s$/', '', $method);
        return $this->$magic($method,$data);
    }

    /**
     * Magic method for setting properties
     * @param string       $property   key name of property
     * @param unknown_type $data       value of property
     * @throws \Exception  triggers error if property does not exist
     */
    public function __set($property,$data)
    {
        $setMthd = 'set'.ucfirst($property);
        $caller  = get_called_class();
        if(in_array($setMthd,$this->_getAllMethods())) {
            $this->$setMthd($data);
            return $this->$property;
        } elseif(in_array($property,$this->_getAllProperties())) {
            $this->$property = $data;
            return $this->$property;
        } else {
            throw new \Exception(__METHOD__." no such property: '$property'");
        }
    }

    /**
     * Magic method accessor for getting properties
     * @param string $property   key name of property
     * @throws \Exception   triggers error if property does not exist
     */
    public function __get($property)
    {
        if(in_array($property,$this->_getAllProperties())) {
            return $this->$property;
        } else {
            throw new \Exception(__METHOD__." no such property: $property");
        }
    }
}
