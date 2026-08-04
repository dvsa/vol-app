<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IRHP Permit Status Trait
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait IrhpPermitStatusOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"irhp_permit_awaiting_printing", "irhp_permit_ceased", "irhp_permit_error", "irhp_permit_expired", "irhp_permit_pending", "irhp_permit_printed", "irhp_permit_printing", "irhp_permit_terminated"}})
     * @Transfer\Optional
     */
    public $status;

    public function getStatus()
    {
        return $this->status;
    }
}
