<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IrhpPermitRangeTo
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeTo
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     *
     * @var int
     */
    protected $toNo;

    /**
     * @return int
     */
    public function getToNo(): int
    {
        return $this->toNo;
    }
}
