<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;
use Zend\Form\FormInterface;

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
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $correspondenceCd = isset($data['licences'][0]['correspondenceCd'])
            ? $data['licences'][0]['correspondenceCd']
            : null;

        $correspondenceAddress = isset($correspondenceCd['address'])
            ? $correspondenceCd['address'] : null;

        $operatorDetails = [
            'id' => $data['id'],
            'version' => $data['version'],
            'name' => $data['name'],
            'operator-type' => isset($data['licences'][0]['goodsOrPsv'])
                ? $data['licences'][0]['goodsOrPsv']['id']
                : null,
            'contactDetailsId' => isset($correspondenceCd['id']) ? $correspondenceCd['id'] : null,
            'contactDetailsVersion' => isset($correspondenceCd['version']) ? $correspondenceCd['version'] : null,
            'trafficArea' => isset($data['licences'][0]['trafficArea'])
                ? $data['licences'][0]['trafficArea']['id']
                : null,
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
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $mapped = [
            'name' => isset($data['operator-details']['name']) ?
                $data['operator-details']['name'] : null,
            'operatorType' => isset($data['operator-details']['operator-type']) ?
                $data['operator-details']['operator-type'] : null,
            'id' => isset($data['operator-details']['id']) ?
                $data['operator-details']['id'] : null,
            'version' => isset($data['operator-details']['version']) ?
                $data['operator-details']['version'] : null,
            'trafficArea' => isset($data['operator-details']['trafficArea']) ?
                $data['operator-details']['trafficArea'] : null,
            'contactDetails' => [
                'id' => isset($data['operator-details']['contactDetailsId']) ?
                    $data['operator-details']['contactDetailsId'] : null,
                'version' => isset($data['operator-details']['contactDetailsVersion']) ?
                    $data['operator-details']['contactDetailsVersion'] : null,
            ],
        ];

        if (isset($data['correspondenceAddress'])) {
            $mapped['contactDetails']['address'] = $data['correspondenceAddress'];
        }

        if (isset($data['contact'])) {
            $mapped['contactDetails']['phoneContacts'] = self::mapPhoneContactsFromForm($data['contact']);
        }
        if (isset($data['contact']['email'])) {
            $mapped['contactDetails']['emailAddress'] = $data['contact']['email'];
        }

        return $mapped;
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
        $operatorDetails = [
            'name',
        ];
        $address = [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'addressLine4',
            'postcode'
        ];
        $formMessages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $operatorDetails)) {
                    $formMessages['operator-details'][$field][] = $message;
                    unset($errors[$field]);
                }
                if (in_array($field, $address)) {
                    $formMessages['correspondenceAddress'][$field][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
