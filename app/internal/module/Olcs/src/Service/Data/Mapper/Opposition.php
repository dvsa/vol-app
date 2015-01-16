<?php

namespace Olcs\Service\Data\Mapper;

/**
 * Class Opposition Data Mapper
 * @author Shaun.Lizzio@valtech.co.uk>
 */
class Opposition
{
    /**
     * Format data on page load
     *
     * @param array $data
     * @return array
     */
    public function formatLoad(array $data)
    {
        if (isset($data['opposer']['contactDetails']['description'])) {
            $data['contactDetailsDescription'] = $data['opposer']['contactDetails']['description'];
        }
        if (isset($data['opposer']['opposerType']['id'])) {
            $data['opposerType'] = $data['opposer']['opposerType']['id'];

        //$data['grounds'] = array_column($data['grounds']['grounds'], 'id');

        return $data;
    }

    /**
     * Format data on form submission
     *
     * @param array $data
     * @return array
     */
    public function formatSave(array $data, $params = array())
    {
        $oppositionData = array();
        $oppositionData['application'] = $params['case']['application']['id'];
        $oppositionData['licence'] = $params['case']['licence']['id'];

        $oppositionData['case'] = $data['base']['case'];
        $oppositionData['isCopied'] = $data['fields']['isCopied'];
        $oppositionData['isInTime'] = $data['fields']['isInTime'];
        $oppositionData['isValid'] = $data['fields']['isValid'];
        $oppositionData['oppositionType'] = $data['fields']['oppositionType'];
        $oppositionData['raisedDate'] = $data['fields']['raisedDate'];
        $oppositionData['validNotes'] = $data['fields']['validNotes'];
        $oppositionData['grounds'] = $data['fields']['grounds'];

        $oppositionData['affectedCentres'] = $data['fields']['affectedCentres'];

        // set up opposer
        $oppositionData['opposer']['opposerType'] = $data['fields']['opposerType'];

        // set up contactDetails
        unset($data['fields']['address']['searchPostcode']);
        $oppositionData['opposer']['contactDetails']['description'] = $data['fields']['contactDetailsDescription'];
        $oppositionData['opposer']['contactDetails']['address'] = $data['fields']['address'];
        $oppositionData['opposer']['contactDetails']['forename'] = $data['fields']['forename'];
        $oppositionData['opposer']['contactDetails']['familyName'] = $data['fields']['familyName'];
        $oppositionData['opposer']['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $oppositionData['opposer']['contactDetails']['contactType'] = 'ct_obj';

        // set up phone contact
        $phoneContact = array();
        $phoneContact['id'] = '';
        $phoneContact['phoneNumber'] = $data['fields']['phone'];
        $phoneContact['phoneContactType'] = 'phone_t_home';

        $oppositionData['opposer']['contactDetails']['phoneContacts'][0] = $phoneContact;

        return ['fields' => $oppositionData];
    }
}
