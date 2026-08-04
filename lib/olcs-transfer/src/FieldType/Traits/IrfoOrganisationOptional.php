<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IrfoOrganisationOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait IrfoOrganisationOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoOrganisation;

    /**
     * @return int
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }
}
