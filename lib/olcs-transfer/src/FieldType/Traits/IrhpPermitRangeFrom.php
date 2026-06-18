<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IrhpPermitRangeFrom
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeFrom
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     *
     * @var int
     */
    protected $fromNo;

    /**
     * @return int
     */
    public function getFromNo(): int
    {
        return $this->fromNo;
    }
}
