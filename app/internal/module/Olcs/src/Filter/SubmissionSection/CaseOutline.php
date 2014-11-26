<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;
use Zend\Filter\AbstractFilter;

/**
 * Class CaseOutline
 * @package Olcs\Filter\SubmissionSection
 */
class CaseOutline extends AbstractFilter
{
    /**
     * @param \Zend\Stdlib\ArrayObject $data
     * @return \Zend\Stdlib\ArrayObject
     * @throws ResourceNotFoundException
     */
    public function filter($data = array())
    {
        return array(
            'outline' => $data['description']
        );
    }
}
