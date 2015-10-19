<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;
use Zend\Form\FormInterface;

/**
 * Class User Mapper
 * @package Olcs\Data\Mapper
 */
class User implements MapperInterface
{
    use MapperTraits\PhoneFieldsTrait;

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData = [];

        if (isset($data['id'])) {
            $formData['id'] = $data['id'];
            $formData['version'] = $data['version'];

            $formData['userLoginSecurity']['loginId'] = $data['loginId'];
            $formData['userLoginSecurity']['accountDisabled'] = $data['accountDisabled'];

            if (!empty($data['lockedDate'])) {
                $formData['userLoginSecurity']['lockedDate'] = date(
                    'd/m/Y H:i:s',
                    strtotime($data['lockedDate'])
                );
            }

            $formData['userType']['userType'] = $data['userType'];

            // get the first role from the list (it should be only one)
            $formData['userType']['role']
                = !empty($data['roles']) ? array_shift($data['roles'])['id'] : null;

            switch ($data['userType']) {
                case 'internal':
                    $formData['userType']['team'] = $data['team']['id'];
                    break;
                case 'transport-manager':
                    $formData['userType']['transportManager'] = $data['transportManager']['id'];
                    break;
                case 'partner':
                    $formData['userType']['partnerContactDetails'] = $data['partnerContactDetails']['id'];
                    break;
                case 'local-authority':
                    $formData['userType']['localAuthority'] = $data['localAuthority']['id'];
                    break;
            }

            $formData['userPersonal']['forename'] = $data['contactDetails']['person']['forename'];
            $formData['userPersonal']['familyName'] = $data['contactDetails']['person']['familyName'];
            $formData['userPersonal']['birthDate'] = $data['contactDetails']['person']['birthDate'];

            if (!empty($data['contactDetails']['phoneContacts'])) {
                $formData['userContactDetails']
                    = self::mapPhoneFieldsFromResult($data['contactDetails']['phoneContacts']);
            }
            $formData['userContactDetails']['emailAddress'] = $data['contactDetails']['emailAddress'];
            $formData['userContactDetails']['emailConfirm'] = $data['contactDetails']['emailAddress'];

            $formData['address'] = $data['contactDetails']['address'];
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
        $commandData['id'] = $data['id'];
        $commandData['version'] = $data['version'];

        $commandData['loginId'] = $data['userLoginSecurity']['loginId'];
        $commandData['mustResetPassword'] = $data['userLoginSecurity']['mustResetPassword'];
        $commandData['accountDisabled'] = $data['userLoginSecurity']['accountDisabled'];

        $commandData['userType'] = $data['userType']['userType'];
        $commandData['roles'] = [$data['userType']['role']];

        switch ($data['userType']['userType']) {
            case 'internal':
                $commandData['team'] = $data['userType']['team'];
                break;
            case 'transport-manager':
                $commandData['application'] = $data['userType']['applicationTransportManagers']['application'];
                $commandData['transportManager'] = $data['userType']['transportManager'];
                break;
            case 'partner':
                $commandData['partnerContactDetails'] = $data['userType']['partnerContactDetails'];
                break;
            case 'local-authority':
                $commandData['localAuthority'] = $data['userType']['localAuthority'];
                break;
            case 'operator':
                $commandData['licenceNumber'] = $data['userType']['licenceNumber'];
                break;
        }

        $commandData['contactDetails']['person']= $data['userPersonal'];
        $commandData['contactDetails']['emailAddress'] = $data['userContactDetails']['emailAddress'];
        $commandData['contactDetails']['phoneContacts'] = self::mapPhoneContactsFromForm($data['userContactDetails']);
        $commandData['contactDetails']['address'] = $data['address'];

        return $commandData;
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
        if (isset($errors['messages']['loginId'])) {
            $messages = [
                'userLoginSecurity' => ['loginId' => $errors['messages']['loginId']]
            ];
            $form->setMessages($messages);
            unset($errors['messages']['loginId']);
        }

        return $errors;
    }
}
