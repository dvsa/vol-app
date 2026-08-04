<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait CancelledReasonOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait CancelledReasonOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":1000})
     */
    protected $cancelledReason;

    /**
     * @return string
     */
    public function getCancelledReason()
    {
        return $this->cancelledReason;
    }
}
