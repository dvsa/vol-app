<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class LeadTcArea
 * @package Olcs\Filter\SubmissionSection
 */
class LeadTcArea extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for lead-tc-area section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        return array(
            'text' => $data['licence']['organisation']['leadTcArea']['name']
        );
    }
}
