<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;

/**
 * Class MyDetails Mapper
 * @package Olcs\Data\Mapper
 */
class MyDetails implements MapperInterface
{
    use MapperTraits\PhoneFieldsTrait;

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['id'] = $data['id'];
        $formData['version'] = $data['version'];

        if (!empty($data['team']['id'])) {
            $formData['userDetails']['team'] = $data['team']['id'];
        }

        if (!empty($data['contactDetails']['person'])) {
            // set person details
            $formData['person'] = $data['contactDetails']['person'];
        }

        if (!empty($data['contactDetails']['phoneContacts'])) {
            // set phone contacts
            $formData['userContact'] = self::mapPhoneFieldsFromResult($data['contactDetails']['phoneContacts']);
        }

        if (!empty($data['contactDetails']['emailAddress'])) {
            // set email fields
            $formData['userContact']['emailAddress'] = $data['contactDetails']['emailAddress'];
            $formData['userContact']['emailConfirm'] = $formData['userContact']['emailAddress'];
        }

        if (!empty($data['contactDetails']['address'])) {
            // set address
            $formData['officeAddress'] = $data['contactDetails']['address'];
        }

        if (!empty($data['translateToWelsh'])) {
            $formData['userSettings']['translateToWelsh'] = $data['translateToWelsh'];
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
        $commandData['id'] = $data['id'];
        $commandData['version'] = $data['version'];

        $commandData['team'] = $data['userDetails']['team'];

        $commandData['contactDetails']['person'] = $data['person'];
        $commandData['contactDetails']['emailAddress'] = $data['userContact']['emailAddress'];

        // set phone contacts
        $commandData['contactDetails']['phoneContacts'] = self::mapPhoneContactsFromForm($data['userContact']);

        if (!empty($data['officeAddress']['addressLine1'])) {
            // set address data
            $commandData['contactDetails']['address'] = $data['officeAddress'];
        }

        $commandData['translateToWelsh'] = $data['userSettings']['translateToWelsh'];

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
