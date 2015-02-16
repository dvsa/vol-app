<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class ComplianceComplaints
 * @package Olcs\Filter\SubmissionSection
 */
class ComplianceComplaints extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for compliance-complaints section
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

            $dataToReturnArray[] = $thisComplaint;
        }

        $filteredData['tables']['compliance-complaints'] = $dataToReturnArray;
        return $filteredData;
    }
}
