<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/4/11
 * Time: 12:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

abstract class DataControllerAbstract extends AbstractActionController
{
    const MAPPER_NAME = '';
    protected static $_mapper;
    protected static $_model;
    protected $_form;

    public function init() {
        self::_setProps();
    }

    //abstract public function indexAction();
    public function indexAction()
    {
        return new ViewModel();
    }

    abstract public function listAction();
    abstract public function editAction();
    abstract public function showAction();
    abstract public function deleteAction();

    // Utility Functions
    /**
     * get a given form and pass optional parameter<br />
     * sets a class property to store the form to make retrieving later faster
     * @throws \Exception  for $formName class not found
     * @param string $formName
     * @param null|void $opt
     * @return Zend_Form
     */
    public function _getForm($formName,$opt=null)
    {
        if($this->_form === null)
        {
            if(class_exists($formName)) {
                $this->_form = new $formName($opt);
            } else {
                throw new \Exception(__METHOD__." form '$formName' class not found");
            }
        }
        return $this->_form;
    }

    /**
     * set useful properties used by the methods inherited from this class based on the $mapperName
     * @return void
     */
    public function _setProps() {
        $dataController = get_called_class();
        $dataController::_getMapper();
        $dataController::_getModel();
    }

    /**
     * accessor to get/set the primary data type (model) used by child view controllers
     * @return string
     */
    public function _getType() {
        $modelName = self::_getModelName();
        return $modelName::_getDataTypeName();
    }

    public function _getModel($input=null) {
        $dataController = get_called_class();
        $modelName = self::_getModelName();
        if(!$dataController::$_model instanceof $modelName) {
            $dataController::$_model = new $modelName();
        }
        return $dataController::$_model;
    }

    public function _getModelName() {
        $dataController = get_called_class();
        $mapperName = self::_getMapperName();
        return $mapperName::_getDataType();
    }

    public static function _getMapper() {
        $dataController = get_called_class();
        $mapperName = self::_getMapperName();
        if(!($dataController::$_mapper instanceof $mapperName)) {
            $dataController::$_mapper = new $mapperName();
        }
        return $dataController::$_mapper;
    }

    public static function _getMapperName() {
        $dataController = get_called_class();
        $mapperName = $dataController::MAPPER_NAME;
        if(is_null($mapperName) || ($mapperName === '')) {
            // try to guess the mapper name from the called class name
            $mapperName = "Application\\Model\\" . preg_replace('/Controller/', '', get_called_class()) . 'Controller';
        }
        if(class_exists($mapperName)) {
            return $mapperName;
        } else {
            throw new \Exception(__METHOD__." Unable to determine data mapper name, please specify");
        }
    }
}