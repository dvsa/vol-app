<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class Operator Business Details Mapper
 * @package Olcs\Data\Mapper
 */
class OperatorBusinessDetails implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $operatorDetails = [
            'id' => $data['id'],
            'version' => $data['version'],
            'name' => $data['name'],
            'isIrfo' => $data['isIrfo'],
            'allowEmail' => $data['allowEmail'],
            'companyNumber' => [
                'company_number' => $data['companyOrLlpNo']
            ]
        ];
        $registeredAddress = $data['contactDetails']['address'] ?? null;

        if (isset($data['organisationPersons']) && count($data['organisationPersons'])) {
            $operatorDetails['firstName'] = $data['organisationPersons'][0]['person']['forename'];
            $operatorDetails['lastName'] = $data['organisationPersons'][0]['person']['familyName'];
            $operatorDetails['personId'] = $data['organisationPersons'][0]['person']['id'];
            $operatorDetails['personVersion'] = $data['organisationPersons'][0]['person']['version'];
        }

        if (isset($data['natureOfBusiness'])) {
            $operatorDetails['natureOfBusiness'] = $data['natureOfBusiness'];
        }

        $formData = [
            'operator-cpid' => ['type' => $data['cpid']['id'] ?? null],
            'operator-business-type' => ['type' => $data['type']['id'] ?? null],
            'operator-details' => $operatorDetails,
            'registeredAddress' => $registeredAddress
        ];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $mapped = [
            'cpid' => !empty($data['operator-cpid']['type']) ?
                $data['operator-cpid']['type'] : null,
            'businessType' => $data['operator-business-type']['type'],
            'companyNumber' => $data['operator-details']['companyNumber']['company_number'] ?? null,
            'name' => $data['operator-details']['name'] ?? null,
            'natureOfBusiness' => $data['operator-details']['natureOfBusiness'] ?? null,
            'firstName' => $data['operator-details']['firstName'] ?? null,
            'lastName' => $data['operator-details']['lastName'] ?? null,
            'personId' => $data['operator-details']['personId'] ?? null,
            'personVersion' => $data['operator-details']['personVersion'] ?? null,
            'id' => $data['operator-details']['id'] ?? null,
            'version' => $data['operator-details']['version'] ?? null,
            'address' => $data['registeredAddress'] ?? null,
            'isIrfo' => $data['operator-details']['isIrfo'] ?? null,
            'allowEmail' => $data['operator-details']['allowEmail'] ?? null
        ];

        return $mapped;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $operatorDetails = [
            'name',
            'natureOfBusiness',
            'firstName',
            'lastName'
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
            if (is_string($fieldErrors)) {
                $fieldErrors = [$fieldErrors];
            }
            foreach ($fieldErrors as $message) {
                if (in_array($field, $operatorDetails)) {
                    $formMessages['operator-details'][$field][] = $message;
                    unset($errors[$field]);
                }
                if ($field == 'companyNumber') {
                    $formMessages['operator-details']['companyNumber']['company-number'][] = $message;
                    unset($errors[$field]);
                }
            }
            if ($field === 'address') {
                foreach ($fieldErrors as $subfieldName => $subfieldErrors) {
                    foreach ($subfieldErrors as $key => $addressError) {
                        if (in_array($subfieldName, $address)) {
                            $formMessages['registeredAddress'][$subfieldName][] = $addressError;
                        }
                    }
                }
                unset($errors['address']);
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
