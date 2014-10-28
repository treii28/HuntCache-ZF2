<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Sub;

interface TitleInterface
{
    public function setTitle($data);
    public function getTitle();
    public function setTitleById($id);
    public function getTitleId();
    public function setTitleByName($name);
    public function getTitleName();
}
