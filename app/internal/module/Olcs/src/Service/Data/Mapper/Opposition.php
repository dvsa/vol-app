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
        $data['contactDetailsDescription'] = $data['opposer']['contactDetails']['description'];

        $data['forename'] = $data['opposer']['contactDetails']['person']['forename'];
        $data['familyName'] = $data['opposer']['contactDetails']['person']['familyName'];
        $data['emailAddress'] = $data['opposer']['contactDetails']['emailAddress'];
        if (isset($data['opposer']['contactDetails']['phoneContacts'][0]['phoneNumber'])) {
            $data['phone'] = $data['opposer']['contactDetails']['phoneContacts'][0]['phoneNumber'];
        }

        if (isset($data['opposer']['contactDetails']['description'])) {
            $data['contactDetailsDescription'] = $data['opposer']['contactDetails']['description'];

        }
        if (isset($data['opposer']['opposerType']['id'])) {
            $data['opposerType'] = $data['opposer']['opposerType']['id'];
        }

        $grounds = array();
        if (isset($data['grounds'])) {
            foreach ($data['grounds'] as $ground) {
                if (isset($ground['id'])) {
                    $grounds[] = $ground['id'];
                }
            }
        }
        $data['grounds'] = $grounds;

        $operatingCentres = array();
        if (isset($data['operatingCentres'])) {
            foreach ($data['operatingCentres'] as $operatingCentre) {
                if (isset($operatingCentre['id'])) {
                    $operatingCentres[] = $operatingCentre['id'];
                }
            }
        }
        $data['operatingCentres'] = $operatingCentres;

        return ['fields' => $data, 'address' => $data['opposer']['contactDetails']['address']];
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
        $oppositionData['notes'] = $data['fields']['notes'];
        $oppositionData['grounds'] = $data['fields']['grounds'];

        $oppositionData['operatingCentres'] = $data['fields']['operatingCentres'];

        // set up opposer
        $oppositionData['opposer']['opposerType'] = $data['fields']['opposerType'];

        // set up contactDetails
        $oppositionData['opposer']['contactDetails']['description'] = $data['fields']['contactDetailsDescription'];
        $oppositionData['opposer']['contactDetails']['address'] = $data['address'];
        $oppositionData['opposer']['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $oppositionData['opposer']['contactDetails']['person']['forename'] = $data['fields']['forename'];
        $oppositionData['opposer']['contactDetails']['person']['familyName'] = $data['fields']['familyName'];
        $oppositionData['opposer']['contactDetails']['person']['familyName'] = $data['fields']['familyName'];
        $oppositionData['opposer']['contactDetails']['contactType'] = 'ct_obj';

        // set up phone contact
        $phoneContact = array();
        $phoneContact['id'] = isset($data['fields']['phoneContactId']) ? $data['fields']['phoneContactId'] : '';
        $phoneContact['phoneNumber'] = $data['fields']['phone'];
        $phoneContact['phoneContactType'] = 'phone_t_home';

        $oppositionData['opposer']['contactDetails']['phoneContacts'] = [$phoneContact];

        return ['fields' => $oppositionData];
    }
}
