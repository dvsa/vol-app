<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Agreed Date
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait AdjournedDateOptional
{
    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
     */
    protected $adjournedDate;

    /**
     * @return string
     */
    public function getAdjournedDate()
    {
        return $this->adjournedDate;
    }
}
