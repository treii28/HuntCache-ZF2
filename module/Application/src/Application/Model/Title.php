<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scottw
 * Date: 11/7/11
 * Time: 12:13 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

use ModelAbstract;

class Title extends ModelAbstract
{
    const PRIMARY_ID_KEY  = 'titleId';
    const LABEL_FIELD_NAME = 'name';
    const DATA_TYPE_NAME   = 'title';
    protected static $_dataMapperName = 'TitleMapper';

    public $titleId;
    public function __construct($data=null) {
        $this->_data = self::$_dataStruct;
        parent::__construct($data);
    }

    public function setTitleId($id) {
        if(intval($id) > 0) {
            $this->titleId = intval($id);
        } else {
            throw new \Exception(__METHOD__." '$id' must be a positive integer");
        }
        return $this->getTitleId();
    }

    public function getTitleId() {
        return intval($this->titleId);
    }
}