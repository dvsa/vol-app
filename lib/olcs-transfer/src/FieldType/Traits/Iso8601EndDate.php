<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait EndDate ISO8601 format
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait Iso8601EndDate
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
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
