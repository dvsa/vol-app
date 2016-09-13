<?php

namespace Olcs\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class PhoneContact implements MapperInterface
{
    const DETAILS = 'details';

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
                'phoneNumber' => isset($data['phoneNumber']) ? $data['phoneNumber'] : null,
                'phoneContactType' => isset($data['phoneContactType']) ? $data['phoneContactType'] : null,
                'contactDetailsId' => $data['contactDetails']['id'],
                'id' => isset($data['id']) ? $data['id'] : null,
                'version' => isset($data['version']) ? $data['version'] : null,
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
        $form->setMessages([self::DETAILS => $errors['messages']]);

        return $errors;
    }
}
