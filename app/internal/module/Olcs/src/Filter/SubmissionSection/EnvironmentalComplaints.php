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
                return strtotime($b['complaintDate']) - strtotime($a['complaintDate']);
            }
        );

        $dataToReturnArray = [];

        foreach ($data['complaints'] as $complaint) {

            $thisComplaint['id'] = $complaint['id'];
            $thisComplaint['version'] = $complaint['version'];
            $thisComplaint['complainantForename'] = $complaint['complainantContactDetails']['forename'];
            $thisComplaint['complainantFamilyName'] = $complaint['complainantContactDetails']['familyName'];
            $thisComplaint['description'] = $complaint['description'];
            $thisComplaint['complaintDate'] = $complaint['complaintDate'];
            $thisComplaint['ocComplaints'] = $complaint['ocComplaints'];
            $thisComplaint['status'] = $complaint['status']['description'];

            $dataToReturnArray[] = $thisComplaint;
        }

        $filteredData['tables']['environmental-complaints'] = $dataToReturnArray;

        return $filteredData;
    }
}
