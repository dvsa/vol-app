<?php
namespace Olcs\Data\Object;

use Zend\Stdlib\ArrayObject;

class Cases extends ArrayObject
{
    public function isTm()
    {
        return (isset($this['transportManager']) && isset($this['transportManager']['id']));
    }
}