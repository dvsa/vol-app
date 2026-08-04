<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Year (optional)
 */
trait YearOptional
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $year;

    public function getYear()
    {
        return (int) $this->year;
    }
}
