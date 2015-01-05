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
            $data,
            function ($a, $b) {
                return strtotime($b['complaintDate']) - strtotime($a['complaintDate']);
            }
        );

        $dataToReturnArray = [];

        foreach ($data as $complainant) {
            $thisComplaint['id'] = $complainant['id'];
            $thisComplaint['version'] = $complainant['version'];
            $thisComplaint['complainantForename'] = $complainant['complainantContactDetails']['person']['forename'];
            $thisComplaint['complainantFamilyName'] = $complainant['complainantContactDetails']['person']['familyName'];
            $thisComplaint['description'] = $complainant['description'];
            $thisComplaint['complaintDate'] = $complainant['complaintDate'];

            $dataToReturnArray[] = $thisComplaint;
        }

        $filteredData['tables']['compliance-complaints'] = $dataToReturnArray;
        return $filteredData;
    }
}
