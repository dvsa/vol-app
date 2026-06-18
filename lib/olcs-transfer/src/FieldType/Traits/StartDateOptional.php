<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait StartDateOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait StartDateOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $startDate;

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
}
