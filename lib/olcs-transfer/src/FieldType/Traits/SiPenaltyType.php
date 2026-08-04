<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SiPenaltyType
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait SiPenaltyType
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $siPenaltyType;

    /**
     * @return int
     */
    public function getSiPenaltyType()
    {
        return $this->siPenaltyType;
    }
}
