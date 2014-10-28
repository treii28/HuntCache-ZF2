<?php

namespace Application\Controller;

use DataControllerAbstract;
//use Zend\View\Model\ViewModel;

class CacheController extends DataControllerAbstract
{
    const MAPPER_NAME = "CacheMapper";
    protected static $_mapper;
    protected static $_model;

    public function indexAction()
    {
        return parent::indexAction();
    }

    public function listAction()
    {
        return new ViewModel();
    }

    public function showAction()
    {
        return new ViewModel();
    }

    public function editAction()
    {
        return new ViewModel();
    }

    public function deleteAction()
    {
        return new ViewModel();
    }


}

