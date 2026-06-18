<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IrfoGvPermitOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait IrfoGvPermitOptional
{
    /**
     * @var int
     * @Transfer\Optional
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
