<?php

/**
 * Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
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
        $result = [];
        if (isset($data['results'])) {
            foreach ($data['results'] as $res) {
                if (isset($res['address'])) {
                    $result[$res['id']] = trim($res['address']['addressLine1'] . ', ' . $res['address']['town'], ', ');
                }
            }
        }
        return $result;
    }
}
