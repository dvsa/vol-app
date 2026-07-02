<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IrhpPermitStockInitialStock
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitStockInitialStock
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     * @Transfer\Optional
     */
    protected $initialStock;

    public function getInitialStock(): string
    {
        return $this->initialStock;
    }
}
