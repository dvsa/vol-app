<?php

namespace Olcs\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class PhoneContact implements MapperInterface
{
    public const DETAILS = 'details';

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Api data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        return [
            self::DETAILS => [
                'phoneNumber' => $data['phoneNumber'] ?? null,
                'phoneContactType' => $data['phoneContactType'] ?? null,
                'contactDetailsId' => $data['contactDetails']['id'],
                'id' => $data['id'] ?? null,
                'version' => $data['version'] ?? null,
            ],
        ];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $formData Form data
     *
     * @return array
     */
    public static function mapFromForm(array $formData)
    {
        return $formData[self::DETAILS];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form
     * @param array         $errors Error messages
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $errMsgs = $errors['messages'];
        if (empty($errMsgs)) {
            return [];
        }

        /** @var \Laminas\Form\Fieldset $formFields */
        $formFields = $form->get(self::DETAILS);

        /** @var \Laminas\Form\Element $field */
        foreach ($formFields as $field) {
            $fldName = $field->getName();
            if (!isset($errMsgs[$fldName])) {
                continue;
            }

            $field->setMessages($errMsgs[$fldName]);
            unset($errMsgs[$fldName]);
        }

        return $errMsgs;
    }
}
