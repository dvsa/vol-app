<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Case Outcomes
 */
trait CaseOutcomesOptional
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     * @Transfer\Optional
     */
    protected $outcomes = [];

    /**
     * @return array
     */
    public function getOutcomes()
    {
        return $this->outcomes;
    }
}
