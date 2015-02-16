<?php
/**
 * Class Cases
 * @package Olcs\Data\Object
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Data\Object;

use Zend\Stdlib\ArrayObject;

/**
 * Class Cases
 * @package Olcs\Data\Object
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class Cases extends ArrayObject
{
    public function isTm()
    {
        return (isset($this['transportManager']) && isset($this['transportManager']['id']));
    }
}
