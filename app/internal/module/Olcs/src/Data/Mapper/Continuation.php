<?php

/**
 * Continuation mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

/**
 * Continuation mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Continuation
{
    public static function mapFromErrors($form, array $errors)
    {
        $formMessages = [];
        if (array_key_exists('totAuthVehicles', $errors)) {
            foreach ($errors['totAuthVehicles'] as $fieldErrors) {
                foreach ($fieldErrors as $fieldError) {
                    $formMessages['fields']['totalVehicleAuthorisation'][] = $fieldError;
                }
            }
            unset($errors['totAuthVehicles']);
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
