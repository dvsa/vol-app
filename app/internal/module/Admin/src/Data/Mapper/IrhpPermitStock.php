<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * IRHP Permit stock mapper
 *
 * @package Admin\Data\Mapper
 */
class IrhpPermitStock implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        return ['permitStockDetails' => $data];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data from form
     *
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        return $data['permitStockDetails'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form interface
     * @param array         $errors array response from errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }

    /**
     * Map country data into array for select form element
     *
     * @param array $countries
     * @return array
     */
    public static function mapCountryOptions(array $countries): array
    {
        $optionData = [];

        foreach ($countries as $country) {
            $optionData[$country['id']] = $country['countryDesc'];
        }

        return $optionData;
    }
}
