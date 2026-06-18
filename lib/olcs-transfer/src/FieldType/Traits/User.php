<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait User
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait User
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $user;

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }
}
