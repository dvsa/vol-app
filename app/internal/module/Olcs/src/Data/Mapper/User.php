<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Dvsa\Olcs\Utils\Helper\DateTimeHelper;
use Laminas\Form\FormInterface;
use Olcs\Data\Mapper\Traits as MapperTraits;
use Olcs\Module;

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
     * @param array $data Data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData = [];

        if (isset($data['id'])) {
            $formData['id'] = $data['id'];
            $formData['version'] = $data['version'];

            $formData['userLoginSecurity']['loginId'] = $data['loginId'];
            $formData['userLoginSecurity']['lastLoggedInOn'] = $data['lastLoggedInOn'];
            $formData['userLoginSecurity']['accountDisabled'] = $data['accountDisabled'];

            if (!empty($data['disabledDate'])) {
                $formData['userLoginSecurity']['disabledDate'] = $data['disabledDate'];
            }

            if (!empty($data['createdOn'])) {
                $formData['userLoginSecurity']['createdOn'] = $data['createdOn'];
            }

            $formData['userLoginSecurity']['locked']
                = !empty($data['lockedOn'])
                    ? sprintf(
                        'Yes on %s',
                        (new \DateTime($data['lockedOn']))->format(Module::$dateTimeSecFormat)
                    )
                    : 'No';

            if (!empty($data['latestPasswordResetEvent'])) {
                $formData['userLoginSecurity']['passwordLastReset'] = sprintf(
                    '%s on %s',
                    $data['latestPasswordResetEvent']['eventData'],
                    DateTimeHelper::format($data['latestPasswordResetEvent']['eventDatetime'], Module::$dateTimeSecFormat)
                );
            }

            $formData['userType']['id'] = $data['id'];
            $formData['userType']['userType'] = $data['userType'];

            // get the first role from the list (it should be only one)
            $formData['userType']['role']
                = !empty($data['roles']) ? array_shift($data['roles'])['role'] : null;

            switch ($data['userType']) {
                case 'internal':
                    $formData['userType']['team'] = $data['team']['id'];
                    $formData['userSettings']['osType'] = $data['osType'];
                    break;
                case 'transport-manager':
                    $formData['userType']['currentTransportManager'] = $data['transportManager']['id'];

                    if (!empty($data['transportManager']['homeCd']['person']['familyName'])) {
                        $formData['userType']['currentTransportManagerName']
                            = $data['transportManager']['homeCd']['person']['forename']
                                . ' ' . $data['transportManager']['homeCd']['person']['familyName'];
                    }
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

            $formData['userSettings']['translateToWelsh'] = $data['translateToWelsh'];
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $commandData['id'] = $data['id'];
        $commandData['version'] = $data['version'];

        $commandData['loginId'] = $data['userLoginSecurity']['loginId'];

        if (!empty($data['userLoginSecurity']['accountDisabled'])) {
            $commandData['accountDisabled'] = $data['userLoginSecurity']['accountDisabled'];
        }

        if (!empty($data['userLoginSecurity']['resetPassword'])) {
            $commandData['resetPassword'] = $data['userLoginSecurity']['resetPassword'];
        }

        $commandData['userType'] = $data['userType']['userType'];
        $commandData['roles'] = [$data['userType']['role']];

        switch ($data['userType']['userType']) {
            case 'internal':
                $commandData['team'] = $data['userType']['team'];
                $commandData['osType'] = $data['userSettings']['osType'];
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

        $commandData['contactDetails']['person'] = $data['userPersonal'];
        $commandData['contactDetails']['emailAddress'] = $data['userContactDetails']['emailAddress'];
        $commandData['contactDetails']['phoneContacts'] = self::mapPhoneContactsFromForm($data['userContactDetails']);
        $commandData['contactDetails']['address'] = $data['address'];

        $commandData['translateToWelsh'] = $data['userSettings']['translateToWelsh'];

        return $commandData;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form
     * @param array         $errors Errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $messages = [];

        if (isset($errors['messages']['loginId'])) {
            $messages['userLoginSecurity']['loginId'] = $errors['messages']['loginId'];
            unset($errors['messages']['loginId']);
        }

        if (isset($errors['messages']['role'])) {
            $messages['userType']['role'] = $errors['messages']['role'];
            unset($errors['messages']['role']);
        }

        if (!empty($messages)) {
            $form->setMessages($messages);
        }

        return $errors;
    }
}
