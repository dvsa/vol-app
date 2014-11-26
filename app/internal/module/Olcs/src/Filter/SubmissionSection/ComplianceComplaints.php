<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;
use Zend\Filter\AbstractFilter;

/**
 * Class ComplianceComplaints
 * @package Olcs\Filter\SubmissionSection
 */
class ComplianceComplaints extends AbstractFilter
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
