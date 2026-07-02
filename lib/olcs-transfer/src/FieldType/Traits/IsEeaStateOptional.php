<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IsEeaState
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait IsEeaStateOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $isEeaState;

    /**
     * @return int
     */
    public function getIsEeaState()
    {
        return $this->isEeaState;
    }
}
