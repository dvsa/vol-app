<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait PresidingTC
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait PresidingTCOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $presidingTc;

    /**
     * @return int
     */
    public function getPresidingTc()
    {
        return $this->presidingTc;
    }
}
