<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Sub;

interface AccountTypeInterface
{
    public function setAccountType($data=null);
    public function getAccountType();
    public function setAccountTypeById($id=null);
    public function getAccountTypeId();
    public function setAccountTypeByName($name);
    public function getAccountTypeName();
}
