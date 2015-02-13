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

        $options = [
            'addressFields' => [
                'addressLine1',
                'addressLine2',
                'addressLine3',
                'addressLine4',
                'town',
                'postcode',
                'countryCode'
            ]
        ];

        return $formatter->format($address, $options);
    }
}
