<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Ids
 */
trait CasesMultiple
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $casesMultiple = [];

    /**
     * @return array
     */
    public function getCasesMultiple()
    {
        return $this->casesMultiple;
    }
}
