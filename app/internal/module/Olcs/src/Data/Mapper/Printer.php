<?php

/**
 * Printer mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Printer mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Printer implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData = [
            'printer-details' => $data
        ];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData = $data['printer-details'];
        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   form
     * @param array         $errors errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $messages = [];
        if (isset($errors['messages']['printerName'])) {
            $messages['printer-details']['printerName'] = $errors['messages']['printerName'];
            unset($errors['messages']['printerName']);
        }
        if (isset($errors['messages']['description'])) {
            $messages['printer-details']['description'] = $errors['messages']['description'];
            unset($errors['messages']['description']);
        }
        $form->setMessages($messages);
        return $errors;
    }
}
