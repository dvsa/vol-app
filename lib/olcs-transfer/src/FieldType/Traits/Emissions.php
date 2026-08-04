<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Emissions
 */
trait Emissions
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $emissions;

    /**
     * @return int
     */
    public function getEmissions()
    {
        return $this->emissions;
    }
}
