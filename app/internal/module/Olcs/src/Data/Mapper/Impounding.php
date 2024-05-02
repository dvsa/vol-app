<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class Impounding Mapper
 * @package Olcs\Data\Mapper
 */
class Impounding implements MapperInterface
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

        if (!empty($formData['fields']['venueOther'])) {
            $formData['fields']['venue'] = 'other';
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
        if ($data['fields']['venue'] != 'other') {
            $data['fields']['venueOther'] = null;
        }

        // must have a case
        $data['fields']['case'] = $data['base']['case'];

        // optionally add id and version for updates
        if (!empty($data['base']['id'])) {
            $data['fields']['id'] = $data['base']['id'];
            $data['fields']['version'] = $data['base']['version'];
        }

        // add the publish flag
        $publish = 'N';

        if (isset($data['form-actions']['publish']) && $data['form-actions']['publish'] !== null) {
            $publish = 'Y';
            $data['fields']['text2'] = $data['fields']['details'];
        }

        $data = $data['fields'];
        $data['publish'] = $publish;

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
