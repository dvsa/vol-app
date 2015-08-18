<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

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

        return [
            'data' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'vrm' => $data['vehicle']['vrm'],
                'platedWeight' => $data['vehicle']['platedWeight'],
                'type' => $data['vehicle']['psvType']['id'],
            ],
        ];

    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     * @todo
     */
    public static function mapFromForm(array $data)
    {
        $mapped = $data['data'];

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
}
