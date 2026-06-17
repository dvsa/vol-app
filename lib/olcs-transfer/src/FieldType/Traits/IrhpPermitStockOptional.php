<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IRHP Permit Stock
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IrhpPermitStockOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irhpPermitStock;

    /**
     * @return int
     */
    public function getIrhpPermitStock()
    {
        return $this->irhpPermitStock;
    }
}
