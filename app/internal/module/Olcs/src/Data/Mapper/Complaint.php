<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class Complaint Mapper
 * @package Olcs\Data\Mapper
 */
class Complaint implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        // must have a case
        $formData['base']['case'] = $data['case'];

        if (isset($data['complainantContactDetails']['person'])) {
            $formData['fields']['complainantForename'] = $data['complainantContactDetails']['person']['forename'];
            $formData['fields']['complainantFamilyName'] = $data['complainantContactDetails']['person']['familyName'];
        }

        // optionally set id and version for updates
        if (isset($data['id'])) {
            $formData['base']['id'] = $data['id'];
            $formData['base']['version'] = $data['version'];
        }

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
        $data['fields']['complainantContactDetails']['person']['forename'] = $data['fields']['complainantForename'];
        $data['fields']['complainantContactDetails']['person']['familyName'] = $data['fields']['complainantFamilyName'];

        $data = $data['fields'];

        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        if (!empty($errors['messages'])) {
            $formFields = $form->get('fields');
            foreach ($formFields as $element) {
                if (array_key_exists($element->getName(), $errors['messages'])) {
                    $element->setMessages($errors['messages'][$element->getName()]);
                    unset($errors['messages'][$element->getName()]);
                }
            }
        }

        return $errors;
    }
}
