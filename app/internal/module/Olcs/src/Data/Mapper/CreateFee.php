<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * CreateFee Mapper
 * @package Olcs\Data\Mapper
 */
class CreateFee implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fee-details'] = $data;

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        return $data['fee-details'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array &$errors)
    {
        if (!empty($errors['messages'])) {
            $errors = $errors['messages'];
            $formFields = $form->get('fee-details');
            foreach ($formFields as $element) {
                if (array_key_exists($element->getName(), $errors)) {
                    $element->setMessages($errors[$element->getName()]);
                    unset($errors[$element->getName()]);
                }
            }
        }

        return $errors;
    }
}
