<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SiPenaltyErruRequested
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait SiPenaltyErruRequested
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $erruPenaltyRequested;

    /**
     * @return int
     */
    public function getErruPenaltyRequested()
    {
        return $this->erruPenaltyRequested;
    }
}
