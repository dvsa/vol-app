<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Application
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Valtech <uk@valtech.co.uk>
 */
trait Application
{
    /**
     * @var int
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
}
