<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * AdjustTransaction Mapper
 * @package Olcs\Data\Mapper
 */
class AdjustTransaction implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['details'] = [
            'paymentType' => $data['paymentMethod']['id'],
            'paymentMethod' => $data['paymentMethod']['description'],
            'received' => $data['amount'],
            'payer' => $data['payerName'],
            'slipNo' => $data['payingInSlipNumber'],
            'chequeNo' => $data['chequePoNumber'],
            'poNo' => $data['chequePoNumber'],
            'chequeDate' => $data['chequePoDate'],
            'id' => $data['id'],
            'version' => $data['version'],
        ];

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
        $details = $data['details'];
        unset($details['paymentType']); // we can never change this so remove it
        unset($details['paymentMethod']);
        return $details;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so
     * they can be added to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array &$errors)
    {
        if (!empty($errors['messages'])) {
            $errors = $errors['messages'];
            $formFields = $form->get('details');
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
