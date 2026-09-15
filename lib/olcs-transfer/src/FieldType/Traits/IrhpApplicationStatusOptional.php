<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IrhpApplicationStatusOptional
 */
trait IrhpApplicationStatusOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"permit_app_awaiting", "permit_app_cancelled", "permit_app_declined", "permit_app_expired", "permit_app_fee_paid", "permit_app_issuing", "permit_app_nys", "permit_app_terminated", "permit_app_uc", "permit_app_unsuccessful", "permit_app_valid", "permit_app_withdrawn"}})
     * @Transfer\Optional
     */
    public $status;

    public function getStatus()
    {
        return $this->status;
    }
}
