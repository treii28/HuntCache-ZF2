<?php
/**
 * Class description
 * @copyright Copyright (c) 2012 Finao Online, LLC. All rights reserved.
 */

namespace Application\Model\Sub;

interface StateInterface
{
    public function setState($data);
    public function getState();
    public function setStateById($id);
    public function getStateId();
    public function setStateByName($name);
    public function getStateName();
}
