<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

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
            'companyNumber' => [
                'company_number' => $data['companyOrLlpNo']
            ]
        ];
        $registeredAddress = isset($data['contactDetails']['address']) ? $data['contactDetails']['address'] : null;

        if (isset($data['organisationPersons']) && count($data['organisationPersons'])) {
            $operatorDetails['firstName'] = $data['organisationPersons'][0]['person']['forename'];
            $operatorDetails['lastName'] = $data['organisationPersons'][0]['person']['familyName'];
            $operatorDetails['personId'] = $data['organisationPersons'][0]['person']['id'];
            $operatorDetails['personVersion'] = $data['organisationPersons'][0]['person']['version'];
        }

        $natureOfBusinesses = [];
        if (isset($data['natureOfBusinesses'])) {
            foreach ($data['natureOfBusinesses'] as $nob) {
                $natureOfBusinesses[] = $nob['id'];
            }
        }
        $operatorDetails['natureOfBusinesses'] = $natureOfBusinesses;

        $formData = [
            'operator-cpid' => ['type' => $data['cpid']['id']],
            'operator-business-type' => ['type' => $data['type']['id']],
            'operator-details' => $operatorDetails,
            'registeredAddress' => $registeredAddress
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
            'cpid' => !empty($data['operator-cpid']['type']) ?
                $data['operator-cpid']['type'] : null,
            'businessType' => $data['operator-business-type']['type'],
            'companyNumber' => isset($data['operator-details']['companyNumber']['company_number']) ?
                $data['operator-details']['companyNumber']['company_number'] : null,
            'name' => isset($data['operator-details']['name']) ?
                $data['operator-details']['name'] : null,
            'natureOfBusiness' => isset($data['operator-details']['natureOfBusinesses']) ?
                $data['operator-details']['natureOfBusinesses'] : null,
            'firstName' => isset($data['operator-details']['firstName']) ?
                $data['operator-details']['firstName'] : null,
            'lastName' => isset($data['operator-details']['lastName']) ?
                $data['operator-details']['lastName'] : null,
            'personId' => isset($data['operator-details']['personId']) ?
                $data['operator-details']['personId'] : null,
            'personVersion' => isset($data['operator-details']['personVersion']) ?
                $data['operator-details']['personVersion'] : null,
            'id' => isset($data['operator-details']['id']) ?
                $data['operator-details']['id'] : null,
            'version' => isset($data['operator-details']['version']) ?
                $data['operator-details']['version'] : null,
            'address' => isset($data['registeredAddress']) ?
                $data['registeredAddress'] : null,
            'isIrfo' => isset($data['operator-details']['isIrfo']) ?
                $data['operator-details']['isIrfo'] : null
        ];

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
            foreach ($fieldErrors as $message) {
                if (in_array($field, $operatorDetails)) {
                    $formMessages['operator-details'][$field][] = $message;
                    unset($errors[$field]);
                }
                if (in_array($field, $address)) {
                    $formMessages['registeredAddress'][$field][] = $message;
                    unset($errors[$field]);
                }
                if ($field == 'companyNumber') {
                    $formMessages['operator-details']['companyNumber']['company-number'][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
