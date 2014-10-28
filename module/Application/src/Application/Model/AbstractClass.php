<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model;

abstract class AbstractClass
{
    private $_ref;
    private $_classMethods;
    private $_classProperties;

    private static $_staticRef;
    private static $_classStaticMethods;
    private static $_classStaticProperties;
    private static $_classConstants;

    public function __construct() {
        $this->_ref = new ReflectionClass($this);
        $this->_setClassInfo();

        self::$_staticRef = new ReflectionClass(get_called_class());
        self::_setStaticClassInfo();
        return;
    }

    public function _getPublicProperties() {
        return $this->_classProperties['public'];
    }
    public function _getPrivateProperties() {
        return $this->_classProperties['private'];
    }
    public function _getProtectedProperties() {
        return $this->_classProperties['protected'];
    }
    public function _getAllProperties() {
        $allProperties = array();
        foreach(array('public','protected','private') as $_t) {
            if(!(is_null($this->_classProperties[$_t])) && is_array($this->_classProperties[$_t]) && (count($this->_classProperties[$_t]) > 0)) {
                $allProperties = array_merge($allProperties,$this->_classProperties[$_t]);
            }
        }
        return $allProperties;
    }

    public function _getPublicMethods() {
        return $this->_classMethods['public'];
    }
    public function _getPrivateMethods() {
        return $this->_classMethods['private'];
    }
    public function _getProtectedMethods() {
        return $this->_classMethods['protected'];
    }
    public function _getAllMethods() {
        $allMethods = array();
        foreach(array('public','protected','private') as $_t) {
            if(!(is_null($this->_classMethods[$_t])) && is_array($this->_classMethods[$_t]) && (count($this->_classMethods[$_t]) > 0)) {
                $allMethods = array_merge($allMethods,$this->_classMethods[$_t]);
            }
        }
        return $allMethods;
    }

    public static function _getClassConstants() {
        return self::$_classConstants;
    }

    public static function _getStaticPublicProperties() {
        return self::$_classStaticProperties['public'];
    }
    public static function _getStaticPrivateProperties() {
        return self::$_classStaticProperties['private'];
    }
    public static function _getStaticProtectedProperties() {
        return self::$_classStaticProperties['protected'];
    }
    public static function _getAllStaticProperties() {
        $allStaticProperties = array();
        foreach(array('public','protected','private') as $_t) {
            if(!(is_null(self::$_classStaticProperties[$_t])) && is_array(self::$_classStaticProperties[$_t]) && (count(self::$_classStaticProperties[$_t]) > 0)) {
                $allStaticProperties = array_merge($allStaticProperties,self::$_classStaticProperties[$_t]);
            }
        }
        return $allStaticProperties;
    }

    public static function _getStaticPublicMethods() {
        return self::$_classStaticMethods['public'];
    }
    public static function _getStaticPrivateMethods() {
        return self::$_classStaticMethods['private'];
    }
    public static function _getStaticProtectedMethods() {
        return self::$_classStaticMethods['protected'];
    }
    public static function _getAllStaticMethods() {
        $allStaticMethods = array();
        foreach(array('public','protected','private') as $_t) {
            if(!(is_null(self::$_classStaticMethods[$_t])) && is_array(self::$_classStaticMethods[$_t]) && (count(self::$_classStaticMethods[$_t]) > 0)) {
                $allStaticMethods = array_merge($allStaticMethods,self::$_classStaticMethods[$_t]);
            }
        }
        return $allStaticMethods;
    }

    private static function _setStaticClassInfo() {
        if(!isset(self::$_classConstants)) {
            self::$_classConstants = self::$_staticRef->getConstants();
        }

        if(self::$_classStaticProperties === null) {
            self::$_classStaticProperties = array(
                'private'   => array(),
                'protected' => array(),
                'public'    => array()
            );
            foreach(self::$_staticRef->getProperties() as $_prop) {
                if($_prop->isStatic()) {
                    foreach(array('public','private','protected') as $_vis) {
                        $_visMthd = 'is'.ucfirst($_vis);
                        if($_prop->$_visMthd()) {
                            array_push(self::$_classStaticProperties[$_vis],$_prop->name);
                        }
                    }
                }
            }
            if(self::$_classStaticMethods === null) {
                self::$_classStaticMethods = array(
                    'private'   => array(),
                    'protected' => array(),
                    'public'    => array()
                );
                foreach(self::$_staticRef->getMethods() as $_meth) {
                    if($_meth->isStatic()&&(__METHOD__ !== $_meth)) {
                        foreach(array('public','private','protected') as $_vis) {
                            $_visMthd = 'is'.ucfirst($_vis);
                            if($_meth->$_visMthd()) {
                                array_push(self::$_classStaticMethods[$_vis],$_meth->name);
                            }
                        }
                    }
                }
            }
        }
    }

    private function _setClassInfo() {
        if($this->_classProperties == null)  {
            $this->_classProperties = array(
                'private'   => array(),
                'protected' => array(),
                'public'    => array()
            );
            foreach($this->_ref->getProperties() as $_prop) {
                if(!($_prop->isStatic())) {
                    foreach(array('public','private','protected') as $_vis) {
                        $_visMthd = 'is'.ucfirst($_vis);
                        if($_prop->$_visMthd()) {
                            array_push($this->_classProperties[$_vis],$_prop->name);
                        }
                    }
                }
            }
            if(!(isset($this->_classMethods))||!(is_array($this->_classMethods))) {
                $this->_classMethods = array(
                    'private'   => array(),
                    'protected' => array(),
                    'public'    => array()
                );
                foreach($this->_ref->getMethods() as $_meth) {
                    if(!($_meth->isStatic())&&(__METHOD__ !== $_meth)) {
                        foreach(array('public','private','protected') as $_vis) {
                            $_visMthd = 'is'.ucfirst($_vis);
                            if($_meth->$_visMthd()) {
                                array_push($this->_classMethods[$_vis],$_meth->name);
                            }
                        }
                    }
                }
            }
        }
    }
}
