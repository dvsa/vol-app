<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class Penalties
 * @package Olcs\Filter\SubmissionSection
 */
class Penalties extends AbstractSubmissionSectionFilter
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
