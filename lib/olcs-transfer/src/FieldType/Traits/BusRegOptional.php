<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait BusReg
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait BusRegOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $busReg;

    /**
     * Get bus reg
     *
     * @return int
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set bus reg
     *
     * @param int $busRegId bus reg id
     *
     * @return void
     */
    public function setBusReg($busRegId)
    {
        $this->busReg = (int) $busRegId;
    }
}
