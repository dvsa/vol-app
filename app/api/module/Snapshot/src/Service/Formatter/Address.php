<?php

/**
 * Address formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Formatter;

/**
 * Address formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Address
{
    protected static $allFields = [
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'addressLine4',
        'town',
        'postcode',
        'countryCode'
    ];

    /**
     * Format an address
     *
     * @param array $data   Data containing address
     * @param array $column Column data
     *
     * @return string
     */
    public static function format($data, $column = [])
    {
        if (isset($column['addressFields'])) {
            if ($column['addressFields'] == 'FULL') {
                $fields = self::$allFields;
            } else {
                $fields = $column['addressFields'];
            }
        } else {
            $fields = [
                'addressLine1',
                'town'
            ];
        }

        $parts = [];

        if ($data instanceof \Dvsa\Olcs\Api\Entity\ContactDetails\Address) {
            $data = [
                'addressLine1' => $data->getAddressLine1(),
                'addressLine2' => $data->getAddressLine2(),
                'addressLine3' => $data->getAddressLine3(),
                'addressLine4' => $data->getAddressLine4(),
                'town' => $data->getTown(),
                'postcode' => $data->getPostcode(),
                'countryCode' => ($data->getCountryCode() !== null ? $data->getCountryCode()->getId() : null)
            ];
        }

        if (isset($data['countryCode']['id'])) {
            $data['countryCode'] = $data['countryCode']['id'];
        } else {
            $data['countryCode'] = null;
        }

        foreach ($fields as $item) {
            if (isset($data[$item]) && !empty($data[$item])) {
                $parts[] = $data[$item];
            }
        }

        return implode(', ', $parts);
    }
}
