<?php

namespace Olcs\Data\Mapper;

/**
 * Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentres
{
    public static function mapFromResult(array $data)
    {
        if (empty($data['results'])) {
            return [];
        }

        $result = [];
        foreach ($data['results'] as $res) {
            if (empty($res['address'])) {
                continue;
            }

            $address = $res['address'];
            $result[$res['id']] = trim($address['addressLine1'] . ', ' . $address['town'], ', ');
        }

        return $result;
    }
}
