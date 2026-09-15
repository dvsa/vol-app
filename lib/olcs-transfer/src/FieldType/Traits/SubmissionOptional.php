<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SubmissionOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait SubmissionOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $submission;

    /**
     * @return int
     */
    public function getSubmission()
    {
        return $this->submission;
    }
}
