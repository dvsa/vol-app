<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * CabotageOptional
 */
trait CabotageOptional
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive": true})
     * @Transfer\Optional
     */
    protected $cabotage;

    /**
     * @return ?int
     */
    public function getCabotage()
    {
        return $this->cabotage;
    }
}
