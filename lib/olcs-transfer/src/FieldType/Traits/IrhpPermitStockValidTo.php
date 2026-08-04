<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IRHP Permit Stock Valid To
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait IrhpPermitStockValidTo
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $validTo;

    /**
     * @return mixed
     */
    public function getValidTo()
    {
        return $this->validTo;
    }
}
