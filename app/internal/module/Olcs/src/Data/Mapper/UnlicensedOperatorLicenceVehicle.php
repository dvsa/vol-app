<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Unlicensed Operator Licence Vehicle Mapper
 * @package Olcs\Data\Mapper
 */
class UnlicensedOperatorLicenceVehicle implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        if (empty($data)) {
            // add
            return [];
        }

        $mapped = [
            'organisation' => self::getFromDataIfSet($data, 'organisation'),
            'data' => [
                'id' => self::getFromDataIfSet($data, 'id'),
                'version' => self::getFromDataIfSet($data, 'version'),
            ]
        ];

        if (isset($data['vehicle'])) {
            $mapped['data'] = array_merge(
                $mapped['data'],
                [
                    'vrm' => self::getFromDataIfSet($data['vehicle'], 'vrm'),
                    'platedWeight' => self::getFromDataIfSet($data['vehicle'], 'platedWeight'),
                ]
            );
        }

        return $mapped;
    }

    /**
     * Map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $mapped = array_merge(
            $data['data'],
            [
                'organisation' => self::getFromDataIfSet($data, 'organisation')
            ]
        );

        return $mapped;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     * @todo
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }

    private static function getFromDataIfSet($data, $field)
    {
        return $data[$field] ?? null;
    }
}
