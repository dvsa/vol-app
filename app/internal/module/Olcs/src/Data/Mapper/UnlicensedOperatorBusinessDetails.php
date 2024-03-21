<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;
use Laminas\Form\FormInterface;

/**
 * Class UnlicensedOperator Business Details Mapper
 * @package Olcs\Data\Mapper
 */
class UnlicensedOperatorBusinessDetails implements MapperInterface
{
    use MapperTraits\PhoneFieldsTrait;

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data From API
     *
     * @return array Form data
     */
    public static function mapFromResult(array $data)
    {
        $correspondenceCd = self::getFromDataIfSet($data['licences'][0], 'correspondenceCd');

        $correspondenceAddress = self::getFromDataIfSet($correspondenceCd, 'address');

        $operatorDetails = [
            'id' => $data['id'],
            'version' => $data['version'],
            'name' => $data['name'],
            'operatorType' => self::getFromDataIfSet($data['licences'][0]['goodsOrPsv'], 'id'),
            'contactDetailsId' => self::getFromDataIfSet($correspondenceCd, 'id'),
            'contactDetailsVersion' => self::getFromDataIfSet($correspondenceCd, 'version'),
            'trafficArea' => self::getFromDataIfSet($data['licences'][0]['trafficArea'], 'id'),
        ];

        $contact = [];
        if ($correspondenceCd) {
            if (!empty($correspondenceCd['phoneContacts'])) {
                // set phone contacts
                $contact = self::mapPhoneFieldsFromResult($correspondenceCd['phoneContacts']);
            }
            $contact['email'] = $correspondenceCd['emailAddress'];
        }

        $formData = [
            'operator-details' => $operatorDetails,
            'correspondenceAddress' => $correspondenceAddress,
            'contact' => $contact,
        ];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data From form
     *
     * @return array API data
     */
    public static function mapFromForm(array $data)
    {
        $mapped = [
            'name' => self::getFromDataIfSet($data['operator-details'], 'name'),
            'operatorType' => self::getFromDataIfSet($data['operator-details'], 'operatorType'),
            'id' => self::getFromDataIfSet($data['operator-details'], 'id'),
            'version' => self::getFromDataIfSet($data['operator-details'], 'version'),
            'trafficArea' => self::getFromDataIfSet($data['operator-details'], 'trafficArea'),
            'contactDetails' => [
                'id' => self::getFromDataIfSet($data['operator-details'], 'contactDetailsId'),
                'version' => self::getFromDataIfSet($data['operator-details'], 'contactDetailsVersion'),
            ],
            'isExempt' => self::getFromDataIfSet($data, 'isExempt')
        ];

        if (isset($data['correspondenceAddress'])) {
            $mapped['contactDetails']['address'] = $data['correspondenceAddress'];
        }

        if (isset($data['contact']['email'])) {
            $mapped['contactDetails']['emailAddress'] = $data['contact']['email'];
        }

        $mapped['contactDetails']['phoneContacts'] = self::mapPhoneContactsFromForm($data['contact']);

        return $mapped;
    }

    private static function getFromDataIfSet($data, $field)
    {
        return $data[$field] ?? null;
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
        $formMessages = [];

        // top level errors
        $formMessages = array_merge(
            $formMessages,
            self::mapOperatorDetailsErrors($errors),
            self::mapAddressErrors($errors),
            self::mapContactErrors($errors)
        );

        $form->setMessages($formMessages);
        return $errors;
    }

    private static function mapOperatorDetailsErrors(&$errors)
    {
        $formMessages = [];
        $operatorDetails = ['name', 'operatorType', 'trafficArea',];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $operatorDetails)) {
                    $formMessages['operator-details'][$field][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        return $formMessages;
    }

    private static function mapAddressErrors(&$errors)
    {
        $formMessages = [];
        $address = [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'addressLine4',
            'town',
            'postcode',
        ];
        if (isset($errors['contactDetails']['address'])) {
            foreach ($errors['contactDetails']['address'] as $field => $fieldErrors) {
                foreach ($fieldErrors as $message) {
                    if (in_array($field, $address)) {
                        $formMessages['correspondenceAddress'][$field][] = $message;
                        unset($errors[$field]);
                    }
                }
            }
        }

        return $formMessages;
    }

    private static function mapContactErrors(&$errors)
    {
        $formMessages = [];
        if (isset($errors['contactDetails']['emailAddress'])) {
            $formMessages['contact']['email'] = $errors['contactDetails']['emailAddress'];
        }

        // contactDetails phoneContact errors
        foreach (['business', 'home', 'mobile', 'fax'] as $type) {
            if (isset($errors['contactDetails'][$type . 'PhoneContact'])) {
                $formMessages['contact']['phone_' . $type] = $errors['contactDetails'][$type . 'PhoneContact'];
            }
        }

        return $formMessages;
    }
}
