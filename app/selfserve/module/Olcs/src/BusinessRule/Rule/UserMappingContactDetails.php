<?php
/**
 * User Mapping Contact Details
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\BusinessRule\Rule;

use Common\BusinessRule\BusinessRuleInterface;

/**
 * User Mapping Contact Details
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserMappingContactDetails implements BusinessRuleInterface
{
    /**
     * Formats the data from what the service gives us, to what the form needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatLoadData($data)
    {
        $output = [];
        $output['main']['id']            = $data['id'];
        $output['main']['version']       = $data['version'];
        $output['main']['loginId']       = $data['loginId'];
        $output['main']['permission']    = $data['permission'];
        $output['main']['currentPermission'] = $data['permission'];
        $output['main']['translateToWelsh']  = $data['translateToWelsh'];

        $output['main']['emailAddress']  = $data['contactDetails']['emailAddress'];
        $output['main']['emailConfirm']  = $data['contactDetails']['emailAddress'];

        $output['main']['familyName']    = $data['contactDetails']['person']['familyName'];
        $output['main']['forename']      = $data['contactDetails']['person']['forename'];

        return $output;
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatSaveData($data)
    {
        $output = [];

        $output['id']      = $data['main']['id'];
        $output['version'] = $data['main']['version'];

        $output['loginId'] = $data['main']['loginId'];
        $output['permission'] = $data['main']['permission'];
        $output['translateToWelsh'] = $data['main']['translateToWelsh'];

        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];

        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['main']['forename'];

        return $output;
    }
}
