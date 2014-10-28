<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Sub;

interface CountryInterface
{
    public function setCountry($data=null);
    public function getCountry();
    public function setCountryById($id=null);
    public function getCountryId();
    public function setCountryByName($name);
    public function getCountryName();
}
