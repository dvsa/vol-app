<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class Opposition Mapper
 * @package Olcs\Data\Mapper
 */
class Opposition implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        // to do

    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        // to check
        
        $data = $data['fields'];

        $data['opposerContactDetails'] = [
            'description' => $data['contactDetailsDescription'],
            'emailAddress' => $data['emailAddress'],
            'person' => [
                'forename' => $data['forename'],
                'familyName' => $data['familyName']
            ],
            'phoneContacts' => [
                0 => [
                    "phoneNumber" => $data['phone'],
                    "phoneContactType" => "phone_t_tel"
                ]
            ]
        ];

        if (isset($data['address'])) {
            $data['opposerContactDetails']['address'] = $data['address'];
        }

        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
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
