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
        $retv['homeAddressId'] = $homeAddress['id'];
        $retv['homeAddressVersion'] = $homeAddress['version'];
        $retv['workAddressLine1'] = $workAddress['addressLine1'];
        $retv['workAddressLine2'] = $workAddress['addressLine2'];
        $retv['workAddressLine3'] = $workAddress['addressLine3'];
        $retv['workAddressLine4'] = $workAddress['addressLine4'];
        $retv['workTown'] = $workAddress['town'];
        $retv['workPostcode'] = $workAddress['postcode'];
        $retv['workCountryCode'] = $workAddress['countryCode'];
        $retv['workAddressId'] = $workAddress['id'];
        $retv['workAddressVersion'] = $workAddress['version'];
        return $retv;
    }

    public static function mapFromErrors($form, array $errors)
    {
        $errors = $errors['messages'];
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
            'homeTown'         => 'town',
            'homePostcode'     => 'postcode',
            'homeCountryCode'  => 'countryCode'
        ];
        $workAddress = [
            'workAddressLine1' => 'addressLine1',
            'workAddressLine2' => 'addressLine2',
            'workAddressLine3' => 'addressLine3',
            'workAddressLine4' => 'addressLine4',
            'workTown'         => 'town',
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

    public static function mapFromResult(array $data)
    {
        $homeAddress = [];
        $workAddress = [];
        $tmDetails = [];
        if (isset($data['homeCd']['address'])) {
            $homeAddress = [
                'id' => $data['homeCd']['address']['id'],
                'version' => $data['homeCd']['address']['version'],
                'addressLine1' => $data['homeCd']['address']['addressLine1'],
                'addressLine2' => $data['homeCd']['address']['addressLine2'],
                'addressLine3' => $data['homeCd']['address']['addressLine3'],
                'addressLine4' => $data['homeCd']['address']['addressLine4'],
                'town' => $data['homeCd']['address']['town'],
                'postcode' => $data['homeCd']['address']['postcode']
            ];

            if (isset($data['homeCd']['address']['countryCode']['id'])) {
                $homeAddress['countryCode'] = $data['homeCd']['address']['countryCode']['id'];
            }
        }
        if (isset($data['workCd']['address'])) {
            $workAddress = [
                'id' => $data['workCd']['address']['id'],
                'version' => $data['workCd']['address']['version'],
                'addressLine1' => $data['workCd']['address']['addressLine1'],
                'addressLine2' => $data['workCd']['address']['addressLine2'],
                'addressLine3' => $data['workCd']['address']['addressLine3'],
                'addressLine4' => $data['workCd']['address']['addressLine4'],
                'town' => $data['workCd']['address']['town'],
                'postcode' => $data['workCd']['address']['postcode']
            ];

            if (isset($data['workCd']['address']['countryCode']['id'])) {
                $workAddress['countryCode'] = $data['workCd']['address']['countryCode']['id'];
            }
        }
        if (isset($data['homeCd']['person'])) {
            $tmDetails['personId'] = $data['homeCd']['person']['id'];
            $tmDetails['personVersion'] = $data['homeCd']['person']['version'];
            $tmDetails['firstName'] = $data['homeCd']['person']['forename'];
            $tmDetails['lastName'] = $data['homeCd']['person']['familyName'];
            $tmDetails['title'] = $data['homeCd']['person']['title']['id'];
            if (isset($data['homeCd']['person']['birthDate'])) {
                $tmDetails['birthDate'] = $data['homeCd']['person']['birthDate'];
            }
            if (isset($data['homeCd']['person']['birthPlace'])) {
                $tmDetails['birthPlace'] = $data['homeCd']['person']['birthPlace'];
            }
        }
        if (isset($data['homeCd'])) {
            $tmDetails['emailAddress'] = $data['homeCd']['emailAddress'];
            $tmDetails['homeCdId'] = $data['homeCd']['id'];
            $tmDetails['homeCdVersion'] = $data['homeCd']['version'];
        }
        if (isset($data['tmType']['id'])) {
            $tmDetails['type'] = $data['tmType']['id'];
        }
        if (isset($data['tmStatus']['id'])) {
            $tmDetails['status'] = $data['tmStatus']['id'];
        }
        if (isset($data['id'])) {
            $tmDetails['id'] = $data['id'];
        }
        if (isset($data['version'])) {
            $tmDetails['version'] = $data['version'];
        }

        if (isset($data['removedDate'])) {
            $tmDetails['removedDate'] = $data['removedDate'];
        }

        return [
            'transport-manager-details' => $tmDetails,
            'home-address' => $homeAddress,
            'work-address' => $workAddress
        ];
    }
}
