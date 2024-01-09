<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Olcs\Module;

/**
 * Class IrfoPsvAuth Mapper
 * @package Olcs\Data\Mapper
 */
class IrfoPsvAuth implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        // Add status description as used for a label
        if (!empty($data['status']['description'])) {
            $formData['fields']['statusHtml'] = $data['status']['description'];
            $formData['fields']['statusDescription'] = $data['status']['description'];
        }

        if (!empty($formData['fields']['createdOn'])) {
            // format createOn date
            $createdOn = new \DateTime($formData['fields']['createdOn']);
            $formData['fields']['createdOnHtml'] = $createdOn->format(Module::$dateFormat);
        }

        // default all copies fields to 0
        $formData['fields'] = array_merge(
            [
                'copiesIssued' => 0,
                'copiesIssuedTotal' => 0,
                'copiesRequired' => 0,
                'copiesRequiredTotal' => 0,
            ],
            $formData['fields']
        );

        // copies fields
        $formData['fields']['copiesIssuedHtml'] = $formData['fields']['copiesIssued'];
        $formData['fields']['copiesIssuedTotalHtml'] = $formData['fields']['copiesIssuedTotal'];

        // calculate NonChargeable field
        $formData['fields']['copiesRequiredNonChargeable']
            = (int)$formData['fields']['copiesRequiredTotal'] - (int)$formData['fields']['copiesRequired'];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
