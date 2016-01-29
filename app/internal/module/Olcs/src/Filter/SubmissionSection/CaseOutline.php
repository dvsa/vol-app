<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class CaseOutline
 * @package Olcs\Filter\SubmissionSection
 */
class CaseOutline extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for case-outline section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        return array(
            'text' => $data['description']
        );
    }
}
