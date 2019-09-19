<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Fee Rate mapper
 *
 * @package Admin\Data\Mapper
 */
class FeeRate implements MapperInterface
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
        $formData = [];
        $formData['fields'] = $data;
        $formData['fields']['idReadOnly'] = $data['id'];
        $formData['fields']['feeType'] = $data['feeType']['id'];
        $formData['fields']['fixedValue'] = round($data['fixedValue']);
        $formData['fields']['annualValue'] = round($data['annualValue']);
        $formData['fields']['fiveYearValue'] = round($data['fiveYearValue']);
        return $formData;
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
        return $data['fields'];
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
}
