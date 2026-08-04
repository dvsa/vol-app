<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is Ni Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
trait IsNiOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $isNi;

    /**
     * @return string
     */
    public function getIsNi()
    {
        return $this->isNi;
    }
}
