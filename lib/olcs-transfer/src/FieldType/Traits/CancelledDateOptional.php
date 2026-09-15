<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Cancelled Date
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait CancelledDateOptional
{
    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $cancelledDate;

    /**
     * @return string
     */
    public function getCancelledDate()
    {
        return $this->cancelledDate;
    }
}
