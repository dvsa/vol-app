<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;

/**
 * Class Opposition Mapper
 * @package Olcs\Data\Mapper
 */
class Opposition implements MapperInterface
{
    use MapperTraits\PhoneFieldsTrait;

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (!empty($data['opposer']['opposerType'])) {
            // set opposer type
            $formData['fields']['opposerType'] = $data['opposer']['opposerType'];
        }

        if (!empty($data['opposer']['contactDetails'])) {
            // set contact details fields
            $opposerContactDetails = $data['opposer']['contactDetails'];

            if (!empty($opposerContactDetails['description'])) {
                // set contact details description field
                $formData['fields']['contactDetailsDescription'] = $opposerContactDetails['description'];
            }

            if (!empty($opposerContactDetails['phoneContacts'])) {
                // set phone contacts
                $formData['contact'] = self::mapPhoneFieldsFromResult($opposerContactDetails['phoneContacts']);
            }

            if (!empty($opposerContactDetails['emailAddress'])) {
                // set email field
                $formData['contact']['email'] = $opposerContactDetails['emailAddress'];
            }

            if (!empty($opposerContactDetails['person'])) {
                // set person fields
                $formData['person'] = $opposerContactDetails['person'];
            }

            if (!empty($opposerContactDetails['address'])) {
                // set address fields
                $formData['address'] = $opposerContactDetails['address'];
            }
        }

        if (!empty($data['case'])) {
            // set case - we need case data to conditionally alter the form
            $formData['case'] = $data['case'];
        }

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
