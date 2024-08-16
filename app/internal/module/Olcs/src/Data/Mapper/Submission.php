<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class Submission Mapper
 *
 * @package Olcs\Data\Mapper
 */
class Submission implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     *
     * @return array $formData
     */
    public static function mapFromResult(array $data)
    {
        // always set assigned date to today as string for field
        $data['assignedDate'] = (new \DateTime('now'))->format('Y-m-d');
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (isset($data['submissionType']['id'])) {
            $snapshot = json_decode($data['dataSnapshot'], true);

            $formData['fields']['submissionSections'] = [
                'submissionType' => $data['submissionType']['id'],
                'sections' => array_keys($snapshot)
            ];
        }
        $readOnlyFields = [];
        foreach (['assignedDate', 'informationCompleteDate'] as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $readOnlyFields[] = $field;
            } elseif (empty($formData['fields'][$field])) {
                $formData['fields'][$field] = new \DateTime('now');
            }
        }
        $formData['readOnlyFields'] = $readOnlyFields;
        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     * 'fields' contains an array called submissionSections which must be mapped as two individual values against the
     * 'fields' array in order to preset the type and sections values. These two fields are in fact one element.
     *
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        if (isset($data['fields']['submissionSections'])) {
            $data['fields'] += $data['fields']['submissionSections'];
        }

        unset($data['fields']['submissionSections']);

        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
