<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;
use Laminas\Form\FormInterface;

/**
 * Class IrhpPermitApplication
 * @package Olcs\Data\Mapper
 */
class IrhpPermitApplication implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $data['fields']['countryIds'] = $data['fields']['countrys'];
        $data['fields']['irhpPermitType'] = RefData::ECMT_PERMIT_TYPE_ID;
        $data['fields']['fromInternal'] = 1;
        unset($data['fields']['countrys']);
        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        unset($form);

        return $errors;
    }

    /**
     * Map retrieved sectors list for form field and set selected value if exists
     *
     * @param $selectedSector
     * @return array
     */
    public static function mapSectors(array $mapData, $selectedSector)
    {
        $valueOptions = [];
        foreach ($mapData['results'] as $option) {
            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['name'],
                'selected' => (!empty($selectedSector) && $option['id'] === $selectedSector)
            ];
        }

        return $valueOptions;
    }
}
