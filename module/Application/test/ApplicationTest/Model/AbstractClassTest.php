<?php
/**
 * Created by PhpStorm.
 * User: swood
 * Date: 10/28/2014
 * Time: 1:35 AM
 */

namespace ApplicationTest\Model;

use Application\Model\AbstractClass;
use PHPUnit_Framework_TestCase;

class AbstractClassTest extends PHPUnit_Framework_TestCase {
    function testAbstractClass() {
        $myClass = new extClass();
        $this->assertInstanceOf(\Application\Model\AbstractClass, $myClass);

        $ccon      = $myClass->_getClassConstants();

        $allStatMeth  = $myClass->_getAllStaticMethods();
        $allStatProp  = $myClass->_getAllStaticProperties();
        $privStatMeth = $myClass->_getStaticPrivateMethods();
        $privStatProp = $myClass->_getStaticPrivateProperties();
        $protStatMeth = $myClass->_getStaticProtectedMethods();
        $protStatProp = $myClass->_getStaticPrivateProperties();
        $pubStatMeth  = $myClass->_getStaticPublicMethods();
        $pubStatProp  = $myClass->_getStaticPublicProperties();

        $privMeth     = $myClass->_getPrivateMethods();
        $privProp     = $myClass->_getPrivateProperties();
        $protMeth     = $myClass->_getProtectedMethods();
        $protProp     = $myClass->_getProtectedProperties();
        $pubMeth      = $myClass->_getPublicMethods();
        $pubProp      = $myClass->_getPublicProperties();

        $allMeth      = $myClass->_getAllMethods();
        $allProp      = $myClass->_getAllProperties();
    }
}

class extClass extends AbstractClass
{
    const CLASSCONST = 'class constant';

    private   $privProp = 'private property';
    protected $protProp = 'protected property';
    public    $pubProp  = 'public property';

    private   static $privStatProp = 'private static property';
    protected static $protStatProp = 'protected static property';
    public    static $pubStatProp  = 'public static property';

    private function privFunc() {
        return $this->privProp;
    }
    protected function protFunc() {
        return $this->protProp;
    }
    public function pubFunc() {
        return $this->pubProp;
    }

    private static function privStatFunc() {
        return self::$privStatProp;
    }
    protected static function protStatFunc() {
        return self::$protStatProp;
    }
    public static function pubStatFunc() {
        return self::$pubStatProp;
    }
}