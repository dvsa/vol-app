<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait EndDateOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait EndDateOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $endDate;

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}
