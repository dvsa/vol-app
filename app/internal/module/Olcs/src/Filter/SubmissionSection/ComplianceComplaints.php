<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;

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
        usort(
            $data,
            function ($a, $b) {
                return strtotime($b['complaintDate']) - strtotime($a['complaintDate']);
            }
        );
        return $data;
    }
}
