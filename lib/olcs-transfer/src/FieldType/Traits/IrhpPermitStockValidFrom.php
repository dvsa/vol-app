<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IRHP Permit Stock Valid Start
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait IrhpPermitStockValidFrom
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $validFrom;

    /**
     * @return mixed
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }
}
