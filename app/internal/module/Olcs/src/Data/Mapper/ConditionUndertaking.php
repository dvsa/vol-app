<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Common\RefData;

/**
 * Class ConditionUndertaking Mapper
 * @package Olcs\Data\Mapper
 */
class ConditionUndertaking implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        if (isset($data['isFulfilled'])) {
            $formData['fields']['fulfilled'] = $data['isFulfilled'];
        }

        if (isset($data['conditionType'])) {
            $formData['fields']['type'] = $data['conditionType'];
        }

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        // set the attached to
        if (isset($data['attachedTo'])) {
            if ($data['attachedTo']['id'] === RefData::ATTACHED_TO_OPERATING_CENTRE) {
                $formData['fields']['attachedTo'] = $data['operatingCentre']['id'];
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
        // optionally add id and version for updates
        if (!empty($data['fields']['id'])) {
            $data['fields']['id'] = $data['fields']['id'];
            $data['fields']['version'] = $data['fields']['version'];
        }

        // set the attached to
        if ($data['fields']['attachedTo'] !== RefData::ATTACHED_TO_LICENCE) {
            $data['fields']['operatingCentre'] = $data['fields']['attachedTo'];
            $data['fields']['attachedTo'] = RefData::ATTACHED_TO_OPERATING_CENTRE;
        }

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
