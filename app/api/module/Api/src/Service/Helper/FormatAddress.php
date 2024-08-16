<?php

/**
 * Takes an address entity and produces a formatted address
 */

namespace Dvsa\Olcs\Api\Service\Helper;

use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;

/**
 * Takes an address entity and produces a formatted address
 *
 * Class FormatAddress
 * @package Dvsa\Olcs\Api\Service\Helper
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class FormatAddress
{
    public function format(AddressEntity $address, $separator = ', ')
    {
        $addressFields = [
            'AddressLine1' => trim($address->getAddressLine1() ?? ''),
            'AddressLine2' => trim($address->getAddressLine2() ?? ''),
            'AddressLine3' => trim($address->getAddressLine3() ?? ''),
            'AddressLine4' => trim($address->getAddressLine4() ?? ''),
            'Town' => trim($address->getTown() ?? ''),
            'Postcode' => trim($address->getPostcode() ?? '')
        ];

        foreach ($addressFields as $field => $value) {
            if (empty($value)) {
                unset($addressFields[$field]);
            }
        }

        return implode($separator, $addressFields);
    }
}
