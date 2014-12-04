<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Image;

class Icon extends ImageAbstract
{
    public $icondir;

    public function __construct ($data=null,$icondir) {
        parent::__construct();
        $this->icondir = self::_cleanIconDir($icondir);
        if(is_string($data) && ($data !== '')) {
            $this->setFromList($data);
        } elseif(is_array($data)) {
            parent::_init($data);
        }
    }

    private static function _cleanIconDir($iDir) {
        return preg_replace("/\/$/",'',$iDir) . "/";
    }

    public function setFromList($fn) {
        $nIcon = self::_getIconFromList($fn,$this->icondir);
        if($nIcon instanceof Icon) {
            $this->filepath = $nIcon->filepath;
            $this->urlpath  = $nIcon->urlpath;
            $this->filename = $nIcon->filename;
            return true;
        } else {
            return false;
        }
    }
    public static function _getIconFromList($fn,$icondir) {
        foreach(self::_getIconList($icondir) as $_i) {
            if($fn === $_i->filename) {
                return $_i;
            }
        }
        return false;
    }

    public static function _getIconList($icondir) {
        $icondir = self::_cleanIconDir($icondir);
        $imgdir = parent::_getCleanedPath($icondir);
        //$imgdir = preg_replace('/(^.*\/)application$/', '$1public/img/'. $icondir, APPLICATION_PATH);
        $iconDir = scandir($imgdir);
        $iconfiles = array();
        foreach($iconDir as $fn) {
            foreach(array('gif','jpg','png') as $ext) {
                if(preg_match('/\.'.$ext.'$/i',$fn)) {
                    $nIcon = new Icon(
                        array(
                            'filepath' => $imgdir,
                            'urlpath'  => "/img/" . $icondir,
                            'filename' => $fn
                        ),
                        $icondir
                    );
                    array_push($iconfiles,$nIcon);
                }
            }
        }
        return $iconfiles;
    }

    public static function _getIconListAsArray($icondir) {
        $icondir = self::_cleanIconDir($icondir);
        $imgdir = parent::_getCleanedPath($icondir);
        //$imgdir = preg_replace('/(^.*\/)application$/', '$1public/img/'. $icondir, APPLICATION_PATH);
        $iconDir = scandir($imgdir);
        $iconfiles = array();
        $baseUrl = rtrim(Zend_Controller_Front::getInstance()->getBaseUrl());
        foreach($iconDir as $fn) {
            foreach(array('gif','jpg','png') as $ext) {
                if(preg_match('/\.'.$ext.'$/i',$fn)) {
                    array_push(
                        $iconfiles,
                        array(
                            'filepath' => $imgdir,
                            'urlpath'  => $baseUrl . "/img/" . $icondir,
                            'filename' => $fn,
                            'icondir'  => $icondir
                        )
                    );
                }
            }
        }
        return $iconfiles;
    }
}
