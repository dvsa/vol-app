<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TransportManagers
 * @package Olcs\Filter\SubmissionSection
 */
class TransportManagers extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for transport-managers section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();
        if (!empty($data['licence']['tmLicences'])) {
            foreach ($data['licence']['tmLicences'] as $tmLicence) {
                $thisRow = array();
                $thisRow['licNo'] = $data['licence']['licNo'];
                $thisRow['id'] = $tmLicence['transportManager']['id'];
                $thisRow['version'] = $tmLicence['transportManager']['version'];
                $thisRow['tmType'] = $tmLicence['transportManager']['tmType']['description'];
                $thisRow['title'] = $tmLicence['transportManager']['workCd']['person']['title'];
                $thisRow['forename'] = $tmLicence['transportManager']['workCd']['person']['forename'];
                $thisRow['familyName'] = $tmLicence['transportManager']['workCd']['person']['familyName'];
                $thisRow['dob'] = $tmLicence['transportManager']['workCd']['person']['birthDate'];

                $thisRow['qualifications'] = array();
                foreach ($tmLicence['transportManager']['qualifications'] as $qualification) {
                    $thisRow['qualifications'][] = $qualification['qualificationType']['description'];
                }

                $thisRow['otherLicences'] = array();

                foreach ($tmLicence['transportManager']['otherLicences'] as $otherLicence) {
                    $thisOtherRow = array();
                    $thisOtherRow['licNo'] = $otherLicence['licNo'];
                    $thisOtherRow['applicationId'] = $otherLicence['application']['id'];
                    $thisRow['otherLicences'][] = $thisOtherRow;
                }

                $dataToReturnArray['tables']['transport-managers'][] = $thisRow;
            }
        }

        return $dataToReturnArray;
    }
}
