<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class Statement Mapper
 * @package Olcs\Data\Mapper
 */
class Statement implements MapperInterface
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

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (isset($data['requestorsContactDetails']['person'])) {
            $formData['fields']['requestorsForename'] = $data['requestorsContactDetails']['person']['forename'];
            $formData['fields']['requestorsFamilyName'] = $data['requestorsContactDetails']['person']['familyName'];
        }

        if (!empty($data['requestorsContactDetails']['address'])) {
            // set address fields
            $formData['requestorsAddress'] = $data['requestorsContactDetails']['address'];
        }

        // optionally set id and version for updates
        if (isset($data['id'])) {
            $formData['base']['id'] = $data['id'];
            $formData['base']['version'] = $data['version'];
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
        $commandData = $data['fields'];

        // set person
        $requestorsContactDetails['person']['forename'] = $data['fields']['requestorsForename'];
        $requestorsContactDetails['person']['familyName'] = $data['fields']['requestorsFamilyName'];

        // set address data
        $requestorsContactDetails['address'] = $data['requestorsAddress'];

        // set requestorsContactDetails
        $commandData['requestorsContactDetails'] = $requestorsContactDetails;

        return $commandData;
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
