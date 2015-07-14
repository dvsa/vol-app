<?php

/**
 * Transport Manager mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Data\Mapper;

/**
 * Transport Manager mapper
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManager
{
    public static function mapFromForm(array $data)
    {
        $tmDetails = $data['transport-manager-details'];
        $homeAddress = $data['home-address'];
        $workAddress = $data['work-address'];

        $retv = $tmDetails;
        $retv['homeAddressLine1'] = $homeAddress['addressLine1'];
        $retv['homeAddressLine2'] = $homeAddress['addressLine2'];
        $retv['homeAddressLine3'] = $homeAddress['addressLine3'];
        $retv['homeAddressLine4'] = $homeAddress['addressLine4'];
        $retv['homeTown'] = $homeAddress['town'];
        $retv['homePostcode'] = $homeAddress['postcode'];
        $retv['homeCountryCode'] = $homeAddress['countryCode'];
        $retv['workAddressLine1'] = $workAddress['addressLine1'];
        $retv['workAddressLine2'] = $workAddress['addressLine2'];
        $retv['workAddressLine3'] = $workAddress['addressLine3'];
        $retv['workAddressLine4'] = $workAddress['addressLine4'];
        $retv['workTown'] = $workAddress['town'];
        $retv['workPostcode'] = $workAddress['postcode'];
        $retv['workCountryCode'] = $workAddress['countryCode'];
        return $retv;
    }

    public static function mapFromErrors($form, array $errors)
    {
        $tmDetails = [
            'firstName',
            'lastName',
            'emailAddress',
            'birthDate',
            'birthPlace',
            'title',
            'type'
        ];
        $homeAddress = [
            'homeAddressLine1' => 'addressLine1',
            'homeAddressLine2' => 'addressLine2',
            'homeAddressLine3' => 'addressLine3',
            'homeAddressLine4' => 'addressLine4',
            'homePostcode'     => 'postcode',
            'homeCountryCode'  => 'countryCode'
        ];
        $workAddress = [
            'workAddressLine1' => 'addressLine1',
            'workAddressLine2' => 'addressLine2',
            'workAddressLine3' => 'addressLine3',
            'workAddressLine4' => 'addressLine4',
            'workPostcode'     => 'postcode',
            'workCountryCode'  => 'countryCode'
        ];
        $formMessages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $message) {
                if (in_array($field, $tmDetails)) {
                    $formMessages['transport-manager-details'][$field][] = $message;
                    unset($errors[$field]);
                }
                if (array_key_exists($field, $homeAddress)) {
                    $formMessages['home-address'][$homeAddress[$field]][] = $message;
                    unset($errors[$field]);
                }
                if (array_key_exists($field, $workAddress)) {
                    $formMessages['work-address'][$workAddress[$field]][] = $message;
                    unset($errors[$field]);
                }
            }
        }
        $form->setMessages($formMessages);
        return $errors;
    }
}
