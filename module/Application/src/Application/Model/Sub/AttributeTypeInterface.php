<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Sub;

interface AttributeTypeInterface
{
    public function setAttributeType($data=null);
    public function getAttributeType();
    public function setAttributeTypeById($id=null);
    public function getAttributeTypeId();
    public function setAttributeTypeByName($name);
    public function getAttributeTypeName();
}
