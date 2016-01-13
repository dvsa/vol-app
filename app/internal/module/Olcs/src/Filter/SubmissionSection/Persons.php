<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class Persons
 * @package Olcs\Filter\SubmissionSection
 */
class Persons extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for person section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();

        if ($data['licence']['organisation']['organisationPersons']) {
            usort(
                $data['licence']['organisation']['organisationPersons'],
                function ($a, $b) {
                    return strnatcmp($a['person']['forename'], $b['person']['forename']);
                }
            );
            foreach ($data['licence']['organisation']['organisationPersons'] as $organisationOwner) {
                $thisOrganisationOwner['id'] = $organisationOwner['person']['id'];
                $thisOrganisationOwner['title'] = $organisationOwner['person']['title']['description'];
                $thisOrganisationOwner['familyName'] = $organisationOwner['person']['familyName'];
                $thisOrganisationOwner['forename'] = $organisationOwner['person']['forename'];
                $thisOrganisationOwner['birthDate'] = $organisationOwner['person']['birthDate'];
                $dataToReturnArray['tables']['persons'][] = $thisOrganisationOwner;

            }
        }
        return $dataToReturnArray;
    }
}
