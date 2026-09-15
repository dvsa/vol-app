<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * PermitsRequired Euro 5
 */
trait RequiredEuro5Optional
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     */
    public $requiredEuro5;

    /**
     * @return int
     */
    public function getRequiredEuro5()
    {
        return (int)$this->requiredEuro5;
    }
}
