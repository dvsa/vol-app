<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class SubmissionAction Mapper
 * @package Olcs\Data\Mapper
 */
class SubmissionAction implements MapperInterface
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

        if (
            !empty($formData['fields']['isDecision']) && ($formData['fields']['isDecision'] === 'Y')
            && !empty($formData['fields']['actionTypes'])
        ) {
            // for decision it is not a multi-select
            $formData['fields']['actionTypes'] = array_shift($formData['fields']['actionTypes']);
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
        if (!is_array($data['fields']['actionTypes'])) {
            // make it an array with a single value
            $data['fields']['actionTypes'] = [$data['fields']['actionTypes']];
        }

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
        return $errors;
    }
}
