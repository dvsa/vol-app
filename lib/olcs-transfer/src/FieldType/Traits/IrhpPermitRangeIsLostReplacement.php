<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Irhp Permit Range Is Lost Replacement
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeIsLostReplacement
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     * @Transfer\Optional
     *
     *  @var int
     */
    protected $lostReplacement;

    /**
     * @return int
     */
    public function getIsLostReplacement()
    {
        return $this->lostReplacement;
    }
}
