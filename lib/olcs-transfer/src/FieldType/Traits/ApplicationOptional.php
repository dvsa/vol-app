<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait ApplicationOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait ApplicationOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param $applicationId Application ID
     */
    public function setApplication($applicationId)
    {
        $this->application = (int) $applicationId;
    }
}
