<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Cabotage
 * @author ONE
 */
trait Cabotage
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $cabotage;

    /**
     * @return int
     */
    public function getCabotage()
    {
        return $this->cabotage;
    }
}
