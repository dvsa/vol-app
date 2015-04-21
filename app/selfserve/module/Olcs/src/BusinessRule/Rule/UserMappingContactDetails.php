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
        $output['main']['memorableWord'] = $data['memorableWord'];
        $output['main']['loginId']       = $data['loginId'];

        $output['main']['emailAddress']  = $data['contactDetails']['emailAddress'];
        $output['main']['emailConfirm']  = $data['contactDetails']['emailAddress'];
        $output['contactDetailsId']      = $data['contactDetails']['id'];
        $output['contactDetailsVersion'] = $data['contactDetails']['version'];

        $output['contactType'] = $data['contactDetails']['contactType']['id'];

        $output['main']['familyName']    = $data['contactDetails']['person']['familyName'];
        $output['main']['forename']      = $data['contactDetails']['person']['forename'];
        $output['main']['birthDate']     = $data['contactDetails']['person']['birthDate'];
        $output['personId']              = $data['contactDetails']['person']['id'];
        $output['personVersion']         = $data['contactDetails']['person']['version'];

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

        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];
        $output['contactDetails']['id']      = $data['contactDetailsId'];
        $output['contactDetails']['version'] = $data['contactDetailsId'];

        if (empty($data['contactType'])) {
            $output['contactDetails']['contactType'] = 'ct_team_user';
        } else {
            $output['contactDetails']['contactType'] = $data['contactType'];
        }

        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['main']['forename'];
        $output['contactDetails']['person']['birthDate']  = $data['main']['birthDate'];
        $output['contactDetails']['person']['id']         = $data['personId'];
        $output['contactDetails']['person']['version']    = $data['personVersion'];

        $output['memorableWord'] = $data['main']['memorableWord'];

        return $output;
    }
}
