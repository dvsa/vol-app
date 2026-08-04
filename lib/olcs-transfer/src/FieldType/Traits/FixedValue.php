<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Fixed Value
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait FixedValue
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     */
    protected $fixedValue;

    /**
     * @return int
     */
    public function getFixedValue()
    {
        return $this->fixedValue;
    }
}
