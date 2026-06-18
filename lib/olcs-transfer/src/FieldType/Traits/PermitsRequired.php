<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * PermitsRequired
 */
trait PermitsRequired
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $permitsRequired;

    /**
     * @return int
     */
    public function getPermitsRequired()
    {
        return $this->permitsRequired;
    }
}
