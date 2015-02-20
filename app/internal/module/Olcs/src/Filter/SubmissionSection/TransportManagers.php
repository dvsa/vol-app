<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TransportManagers
 * @package Olcs\Filter\SubmissionSection
 */
class TransportManagers extends AbstractSubmissionSectionFilter
{
    private $dataToReturnArray = array('tables' => array('transport-managers' => array()));

    /**
     * Filters data for transport-managers section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {

        if (!empty($data['licence']['tmLicences'])) {
            $this->extractTmData(
                $data['licence']['tmLicences'],
                $data['licence']['licNo']
            );
        }

        if (!empty($data['licence']['organisation']['licences'])) {
            foreach ($data['licence']['organisation']['licences'] as $licence) {
                if (!empty($licence['applications'])) {
                    foreach ($licence['applications'] as $application) {
                        if (!empty($application['transportManagers'])) {
                            $this->extractTmData(
                                $application['transportManagers'],
                                $application['licence']['licNo']
                            );
                        }
                    }
                }
            }
        }

        return $this->dataToReturnArray;
    }

    private function extractTmData($data, $licenceNo)
    {
        if (!empty($data)) {
            foreach ($data as $tmData) {
                $thisRow = array();
                $thisRow['licNo'] = $licenceNo;
                $thisRow['id'] = $tmData['transportManager']['id'];
                $thisRow['version'] = $tmData['transportManager']['version'];
                $thisRow['tmType'] = $tmData['transportManager']['tmType']['description'];
                $thisRow['title'] = $tmData['transportManager']['workCd']['person']['title'];
                $thisRow['forename'] = $tmData['transportManager']['workCd']['person']['forename'];
                $thisRow['familyName'] = $tmData['transportManager']['workCd']['person']['familyName'];
                $thisRow['dob'] = $tmData['transportManager']['workCd']['person']['birthDate'];

                $thisRow['qualifications'] = array();
                foreach ($tmData['transportManager']['qualifications'] as $qualification) {
                    $thisRow['qualifications'][] = $qualification['qualificationType']['description'];
                }

                $thisRow['otherLicences'] = array();

                foreach ($tmData['transportManager']['otherLicences'] as $otherLicence) {
                    $thisOtherRow = array();
                    $thisOtherRow['licNo'] = $otherLicence['licNo'];
                    $thisOtherRow['applicationId'] = $otherLicence['application']['id'];
                    $thisRow['otherLicences'][] = $thisOtherRow;
                }

                $this->dataToReturnArray['tables']['transport-managers'][] = $thisRow;
            }
        }
    }
}
