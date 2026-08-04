<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Year
 */
trait Year
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $year;

    public function getYear()
    {
        return $this->year;
    }
}
