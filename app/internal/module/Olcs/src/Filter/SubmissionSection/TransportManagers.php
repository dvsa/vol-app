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
        var_dump($data);exit;
        $dataToReturnArray = array();
        if (!empty($data['licence']['applications'])) {
            foreach ($data['licence']['applications'] as $application) {
                $thisRow = array();

                foreach ($application['tmApplications'] as $tmApplication) {
                    $thisRow['id'] = $tmApplication['transportManager']['id'];
                    $thisRow['version'] = $tmApplication['transportManager']['version'];
                    $thisRow['application_id'] = $application['id'];

                    $thisRow['forename'] = $tmApplication['transportManager']['forename'];
                    $thisRow['familyName'] = $tmApplication['transportManager']['familyName'];
                    $thisRow['dob'] = $tmApplication['transportManager']['birthDate'];

                    foreach ($tmApplication['qualifications'] as $qualification) {
                        $thisRow['qualifications'][] = $qualification['qualificationType'];
                    }

                    $thisRow['internal_external'] = $tmApplication['transportManager']['birthDate'];

                    $dataToReturnArray['tables']['prohibition-history'][] = $thisRow;
            }
        }
        $dataToReturnArray['text'] = isset($data['prohibitionNote']) ? $data['prohibitionNote'] : '';
        return $dataToReturnArray;
    }
}
