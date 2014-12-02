<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class AnnualTestHistory
 * @package Olcs\Filter\SubmissionSection
 */
class AnnualTestHistory extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for annual-test-history section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        return array(
            'annualTestHistory' => $data['annualTestHistory']
        );
    }
}
