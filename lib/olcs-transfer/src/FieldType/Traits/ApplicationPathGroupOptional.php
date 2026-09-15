<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait ApplicationPathGroupOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait ApplicationPathGroupOptional
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $applicationPathGroup;

    /**
     * @return int
     */
    public function getApplicationPathGroup()
    {
        return $this->applicationPathGroup;
    }
}
