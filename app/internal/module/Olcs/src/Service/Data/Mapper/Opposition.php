<?php

namespace Olcs\Service\Data\Mapper;

/**
 * Class Opposition Data Mapper
 * @author Shaun Lizzio <Shaun.Lizzio@valtech.co.uk>
 */
class Opposition
{
    /**
     * Format data on page load
     *
     * @param array $data
     * @return array
     */
    public function formatLoad(array $data, $params = array())
    {
        $data['contactDetailsDescription'] = $data['opposer']['contactDetails']['description'];

        // set up opposer
        $data['opposerId'] = $data['opposer']['id'];
        $data['opposerVersion'] = $data['opposer']['version'];
        if (isset($data['opposer']['opposerType']['id'])) {
            $data['opposerType'] = $data['opposer']['opposerType']['id'];
        }

        // set up contactDetails
        $data['contactDetailsId'] = $data['opposer']['contactDetails']['id'];
        $data['contactDetailsVersion'] = $data['opposer']['contactDetails']['version'];
        $data['contactDetailsDescription'] = $data['opposer']['contactDetails']['description'];
        $data['emailAddress'] = $data['opposer']['contactDetails']['emailAddress'];

        // set up person
        $data['personId'] = $data['opposer']['contactDetails']['person']['id'];
        $data['personVersion'] = $data['opposer']['contactDetails']['person']['version'];
        $data['forename'] = $data['opposer']['contactDetails']['person']['forename'];
        $data['familyName'] = $data['opposer']['contactDetails']['person']['familyName'];

        // set up phoneContacts
        if (isset($data['opposer']['contactDetails']['phoneContacts'][0]['phoneNumber'])) {
            $data['phoneContactId'] = $data['opposer']['contactDetails']['phoneContacts'][0]['id'];
            $data['phoneContactVersion'] = $data['opposer']['contactDetails']['phoneContacts'][0]['version'];
            $data['phone'] = $data['opposer']['contactDetails']['phoneContacts'][0]['phoneNumber'];
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

        $caseId = $params['case']['id'];
        $data['case'] = $caseId;

        return array_merge($data, ['fields' => $data, 'address' => $data['opposer']['contactDetails']['address']]);
    }

    /**
     * Format data on form submission
     *
     * @param array $data
     * @param array $params
     * @return array
     */
    public function formatSave(array $data, $params = array())
    {
        $oppositionData = array();

        // set up main opposition data
        if (!(empty($data['fields']['id'])) && !(empty($data['fields']['version']))) {
            $oppositionData['id'] = $data['fields']['id'];
            $oppositionData['version'] = $data['fields']['version'];
        }
        $oppositionData['case'] = $data['fields']['case'];
        $oppositionData['isCopied'] = $data['fields']['isCopied'];
        $oppositionData['isInTime'] = $data['fields']['isInTime'];
        $oppositionData['isWillingToAttendPi'] = $data['fields']['isWillingToAttendPi'];
        $oppositionData['isValid'] = $data['fields']['isValid'];
        $oppositionData['oppositionType'] = $data['fields']['oppositionType'];
        $oppositionData['raisedDate'] = $data['fields']['raisedDate'];
        $oppositionData['validNotes'] = $data['fields']['validNotes'];
        $oppositionData['notes'] = $data['fields']['notes'];

        $oppositionData['application'] = $params['case']['application']['id'];
        $oppositionData['licence'] = $params['case']['licence']['id'];

        $oppositionData['grounds'] = $data['fields']['grounds'];
        $oppositionData['operatingCentres'] = $data['fields']['operatingCentres'];

        // set up opposer
        if (!(empty($data['fields']['opposerId'])) && !(empty($data['fields']['opposerVersion']))) {
            $oppositionData['opposer']['id'] = $data['fields']['opposerId'];
            $oppositionData['opposer']['version'] = $data['fields']['opposerVersion'];
        }
        $oppositionData['opposer']['opposerType'] = $data['fields']['opposerType'];

        // set up contactDetails

        if (!(empty($data['fields']['contactDetailsId'])) && !(empty($data['fields']['contactDetailsVersion']))) {
            $oppositionData['opposer']['contactDetails']['id'] = $data['fields']['contactDetailsId'];
            $oppositionData['opposer']['contactDetails']['version'] = $data['fields']['contactDetailsVersion'];
        }
        $oppositionData['opposer']['contactDetails']['description'] = $data['fields']['contactDetailsDescription'];
        $oppositionData['opposer']['contactDetails']['address'] = $data['address'];
        $oppositionData['opposer']['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $oppositionData['opposer']['contactDetails']['contactType'] = 'ct_obj';

        // set up person
        $oppositionData['opposer']['contactDetails']['person']['id'] = $data['fields']['personId'];
        $oppositionData['opposer']['contactDetails']['person']['version'] = $data['fields']['personVersion'];
        $oppositionData['opposer']['contactDetails']['person']['forename'] = $data['fields']['forename'];
        $oppositionData['opposer']['contactDetails']['person']['familyName'] = $data['fields']['familyName'];

        $phoneContact = [];
        if (!(empty($data['fields']['phoneContactId'])) && !(empty($data['fields']['phoneContactVersion']))) {
            $phoneContact['id'] = $data['fields']['phoneContactId'];
            $phoneContact['version'] = $data['fields']['phoneContactVersion'];
        }
        if (!(empty($data['fields']['contactDetailsId'])) && !(empty($data['fields']['contactDetailsId']))) {
            $phoneContact['contactDetails'] = $data['fields']['contactDetailsId'];
        }
        $phoneContact['contactDetails'] = $data['fields']['contactDetailsId'];
        $phoneContact['phoneNumber'] = $data['fields']['phone'];
        $phoneContact['phoneContactType'] = 'phone_t_tel';

        $oppositionData['opposer']['contactDetails']['phoneContacts'][] = $phoneContact;

        $oppositionData['_OPTIONS_'] = array(
            'cascade' => array(
                'single' => array(
                    'opposer' => array(
                        'entity' => 'Opposer'
                    )
                )
            )
        );

        $oppositionData['opposer']['_OPTIONS_'] = array(
            'cascade' => array(
                'single' => array(
                    'contactDetails' => array(
                        'entity' => 'ContactDetails'
                    )
                )
            )
        );
        $oppositionData['opposer']['contactDetails']['_OPTIONS_'] = array(
            'cascade' => array(
                'list' => array(
                    'phoneContacts' => array(
                        'entity' => 'PhoneContact',
                        'parent' => 'contactDetails'
                    )
                )
            )
        );

        return ['fields' => $oppositionData];
    }
}
