<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;

/**
 * Class IrfoDetails Mapper
 * @package Olcs\Data\Mapper
 */
class IrfoDetails implements MapperInterface
{
    use MapperTraits\PhoneFieldsTrait;

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from API
     *
     * @return array Form data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        if (!empty($formData['fields']['id'])) {
            // set id for HTML element
            $formData['fields']['idHtml'] = $formData['fields']['id'];
        }

        if (!empty($data['irfoContactDetails']['address'])) {
            // set address fields
            $formData['address'] = $data['irfoContactDetails']['address'];
        }

        if (!empty($data['irfoContactDetails']['phoneContacts'])) {
            // set phone contacts
            $formData['contact'] = self::mapPhoneFieldsFromResult($data['irfoContactDetails']['phoneContacts']);
        }

        if (!empty($data['irfoContactDetails']['emailAddress'])) {
            // set contact fields
            $formData['contact']['email'] = $data['irfoContactDetails']['emailAddress'];
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData = $data['fields'];

        $hasData = function ($v) {
            if (!empty($v['name'])) {
                return true;
            }
            return false;
        };

        if (!empty($commandData['irfoPartners'])) {
            // filter out empty irfoPartners
            $commandData['irfoPartners'] = array_filter($commandData['irfoPartners'], $hasData);
        }

        if (!empty($commandData['tradingNames'])) {
            // filter out empty tradingNames
            $commandData['tradingNames'] = array_filter($commandData['tradingNames'], $hasData);
        }

        $irfoContactDetails = [];

        if (!empty($data['contact']['email'])) {
            // set contact fields
            $irfoContactDetails['emailAddress'] = $data['contact']['email'];
        }

        if (!empty($data['address']['addressLine1'])) {
            // set address data
            $irfoContactDetails['address'] = $data['address'];
        }

        // set phone contacts
        $irfoContactDetails['phoneContacts'] = self::mapPhoneContactsFromForm($data['contact']);

        // set irfoContactDetails
        $commandData['irfoContactDetails'] = $irfoContactDetails;

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
