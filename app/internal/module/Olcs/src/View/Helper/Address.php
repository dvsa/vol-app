<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Common\Service\Table\Formatter\Address as AddressFormatter;

/**
 * Class Markers
 * @package Olcs\View\Helper
 */
class Address extends AbstractHelper
{
    /**
     * @param $address
     * @return string
     */
    public function __invoke(array $address)
    {
        $formatter = new AddressFormatter();

        return $formatter->format($address);
    }
}
