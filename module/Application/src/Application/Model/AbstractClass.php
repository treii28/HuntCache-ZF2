<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model;

use ARClassInfo;

abstract class AbstractClass
{
    private $_ref;
    private $_classInfo;
    private $_classMethods;
    private $_classProperties;

    private static $_staticRef;
    private static $_classStaticInfo;
    private static $_classStaticMethods;
    private static $_classStaticProperties;
    private static $_classConstants;

    private        $_rootInfo;
    private static $_rootStaticInfo;

    public function __construct($excludeRoot=true) {
        $this->_ref = new \ReflectionClass($this);
        $this->_classInfo = new ARClassInfo(false);
        $this->_setClassInfo($excludeRoot);

        self::$_staticRef = new \ReflectionClass(get_called_class());
        self::$_classStaticInfo = new ARClassInfo(true);
        self::_setStaticClassInfo($excludeRoot);
        return;
    }

    public function _getPublicProperties() {
        return $this->_classInfo->properties->public;
    }
    public function _getPrivateProperties() {
        return $this->_classInfo->properties->private;
    }
    public function _getProtectedProperties() {
        return $this->_classInfo->properties->protected;
    }
    public function _getAllProperties() {
        $allProperties = array();
        foreach(array('public','protected','private') as $_t) {
            array_merge($allProperties,$this->_classInfo->properties->{$_t});
        }
        return $allProperties;
    }

    public function _getPublicMethods() {
        return $this->_classInfo->methods->public;
    }
    public function _getPrivateMethods() {
        return $this->_classInfo->methods->private;
    }
    public function _getProtectedMethods() {
        return $this->_classInfo->methods->protected;
    }
    public function _getAllMethods() {
        $allMethods = array();
        foreach(array('public','protected','private') as $_t) {
            $allMethods = array_merge($allMethods,$this->_classInfo->methods->{$_t});
        }
        return $allMethods;
    }

    public static function _getClassConstants() {
        return self::$_classStaticInfo->constants;
    }

    public static function _getStaticPublicProperties() {
        return self::$_classStaticInfo->properties->public;
    }
    public static function _getStaticPrivateProperties() {
        return self::$_classStaticInfo->properties->private;
    }
    public static function _getStaticProtectedProperties() {
        return self::$_classStaticInfo->properties->protected;
    }
    public static function _getAllStaticProperties() {
        $allStaticProperties = array();
        foreach(array('public','protected','private') as $_t) {
            $allStaticProperties = array_merge($allStaticProperties,self::$_classStaticInfo->properties->{$_t});
        }
        return $allStaticProperties;
    }

    public static function _getStaticPublicMethods() {
        return self::$_classStaticInfo->methods->public;
    }
    public static function _getStaticPrivateMethods() {
        return self::$_classStaticInfo->methods->private;
    }
    public static function _getStaticProtectedMethods() {
        return self::$_classStaticInfo->methods->protected;
    }
    public static function _getAllStaticMethods() {
        $allStaticMethods = array();
        foreach(array('public','protected','private') as $_t) {
            $allStaticMethods = array_merge($allStaticMethods,self::$_classStaticInfo->methods->{$_t});
        }
        return $allStaticMethods;
    }

    private static function _setStaticClassInfo($excludeRoot=true) {
        $rootInfo = (($excludeRoot) ?
            self::_getRootInfo('static')
            :
            array('properties'=>array('private'=>array(),'protected'=>array(),'public'=>array()),'methods'=>array('private'=>array(),'protected'=>array(),'public'=>array()))
        );

        self::$_classConstants = self::$_staticRef->getConstants();

        if(self::$_classStaticProperties === null) {
            self::$_classStaticProperties = array(
                'private'   => array(),
                'protected' => array(),
                'public'    => array()
            );
            foreach(self::$_staticRef->getProperties() as $_prop) {
                foreach(array('public','private','protected') as $_vis) {
                    if (($_prop->isStatic()) && (($excludeRoot) && !in_array($_prop,$rootInfo['properties'][$_vis]))) {
                        $_visMthd = 'is' . ucfirst($_vis);
                        if ($_prop->$_visMthd()) {
                            array_push(self::$_classStaticProperties[$_vis], $_prop->name);
                        }
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


    private function _setClassInfo($excludeRoot=true)
    {
        $rootInfo = (($excludeRoot) ?
            $this->_getRootInfo()
            :
            array('properties'=>array('private'=>array(),'protected'=>array(),'public'=>array()),'methods'=>array('private'=>array(),'protected'=>array(),'public'=>array()))
        );

        $this->_classProperties = array(
            'private' => array(),
            'protected' => array(),
            'public' => array()
        );

        foreach ($this->_ref->getProperties() as $_prop) {
            foreach (array('public', 'private', 'protected') as $_vis) {
                if (!($_prop->isStatic()) && (($excludeRoot) && !in_array($_prop,$rootInfo['properties'][$_vis]))) {
                    $_visMthd = 'is' . ucfirst($_vis);
                    if ($_prop->$_visMthd()) {
                        array_push($this->_classProperties[$_vis], $_prop->name);
                    }
                }
            }
        }

        $this->_classMethods = array(
            'private' => array(),
            'protected' => array(),
            'public' => array()
        );

        foreach ($this->_ref->getMethods() as $_meth) {
            if (!($_meth->isStatic()) && (__METHOD__ !== $_meth)) {
                foreach (array('public', 'private', 'protected') as $_vis) {
                    if (!($_meth->isStatic()) && (($excludeRoot) && !in_array($_meth,$rootInfo['methods'][$_vis]))) {
                        $_visMthd = 'is' . ucfirst($_vis);
                        if ($_meth->$_visMthd()) {
                            array_push($this->_classMethods[$_vis], $_meth->name);
                        }
                    }
                }
            }
        }
    }

    private static function _getRootInfo($static=null) {
        if($static == 'static') {
            $static = true;
        }

        $rootInfo = array(
            'properties' =>
                array(
                    'private'   => array(),
                    'protected' => array(),
                    'public'    => array()
                ),
            'methods' =>
                array(
                    'private'   => array(),
                    'protected' => array(),
                    'public'    => array()
                ),
        );


        $rootClass = __CLASS__;
        $rootRef = new \ReflectionClass('\Application\Model\AbstractClass');

        foreach($rootRef->getProperties() as $_prop) {
            foreach (array('public', 'private', 'protected') as $_vis) {
                $_visMthd = 'is' . ucfirst($_vis);
                if((
                        (($static) && ($_prop->isStatic())) ||
                        (!($static) && !($_prop->isStatic()))
                    ) && ($_prop->$_visMthd())) {
                    array_push($rootInfo['properties'][$_vis], $_prop->name);
                }
            }
        }
        foreach($rootRef->getMethods() as $_meth) {
            foreach (array('public', 'private', 'protected') as $_vis) {
                $_visMthd = 'is' . ucfirst($_vis);
                if((
                        (($static == 'static') && ($_meth->isStatic())) ||
                        (($static != 'static') && !($_meth->isStatic()))
                    ) && ($_meth->$_visMthd())) {
                    array_push($rootInfo['properties'][$_vis], $_meth->name);
                }
            }
        }
        return $rootInfo;
    }
}

class ARVisibility
{
    public $public    = array();
    public $private   = array();
    public $protected = array();
}