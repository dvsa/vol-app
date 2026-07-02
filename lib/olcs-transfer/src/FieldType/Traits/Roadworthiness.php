<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Roadworthiness
 */
trait Roadworthiness
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 1})
     */
    protected $roadworthiness;

    /**
     * @return int
     */
    public function getRoadworthiness()
    {
        return $this->roadworthiness;
    }
}
