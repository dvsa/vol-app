<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class EnvironmentalComplaints
 * @package Olcs\Filter\SubmissionSection
 */
class EnvironmentalComplaints extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for environmental-complaints section
     * @param array $data
     * @return array $data
     */
    public function filter($data = array())
    {
        $filteredData = array();
        usort(
            $data['complaints'],
            function ($a, $b) {
                return strtotime($a['complaintDate']) - strtotime($b['complaintDate']);
            }
        );

        $dataToReturnArray = [];

        foreach ($data['complaints'] as $complaint) {
            $thisComplaint['id'] = $complaint['id'];
            $thisComplaint['version'] = $complaint['version'];
            $thisComplaint['complainantForename'] = $complaint['complainantContactDetails']['person']['forename'];
            $thisComplaint['complainantFamilyName'] = $complaint['complainantContactDetails']['person']['familyName'];
            $thisComplaint['description'] = $complaint['description'];
            $thisComplaint['complaintDate'] = $complaint['complaintDate'];
            $thisComplaint['ocComplaints'] = $complaint['ocComplaints'];
            $thisComplaint['closeDate'] = $complaint['closeDate'];
            $thisComplaint['status'] = $complaint['status']['description'];

            $dataToReturnArray[] = $thisComplaint;
        }
        $filteredData['tables']['environmental-complaints'] = $dataToReturnArray;

        return $filteredData;
    }
}
