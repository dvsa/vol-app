<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * PermitsRequired Euro 6
 */
trait RequiredEuro6Optional
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     */
    public $requiredEuro6;

    /**
     * @return int
     */
    public function getRequiredEuro6()
    {
        return (int)$this->requiredEuro6;
    }
}
