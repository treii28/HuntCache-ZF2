<?php
/**
 * Class description
 * @copyright Copyright (c) 2011 Finao Online, LLC. All rights reserved.
 */

class Application\Model\Email extends Zend_Mail
{
    public function setFrom($email='treii28@yahoo.com',$name='TheWildWebster') {
        parent::setFrom($email,$name);
    }

    public function send($transport=null) {
        if(is_null($this->_from)||($this->_from==='')) {
            $this->setFrom();
        }
        parent::send($transport);
    }
}
