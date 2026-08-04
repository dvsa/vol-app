<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IrfoGvPermit
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait IrfoGvPermit
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoGvPermit;

    /**
     * @return int
     */
    public function getIrfoGvPermit()
    {
        return $this->irfoGvPermit;
    }
}
