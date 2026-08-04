<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Pi
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Pi
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $pi;

    /**
     * @return int
     */
    public function getPi()
    {
        return $this->pi;
    }
}
