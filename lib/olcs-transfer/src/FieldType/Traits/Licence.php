<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Licence
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Licence
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }
}
