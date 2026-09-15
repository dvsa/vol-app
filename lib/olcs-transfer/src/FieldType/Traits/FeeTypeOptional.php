<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Fee Type Optional
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait FeeTypeOptional
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     * @Transfer\Optional
     */
    protected $feeType;

    /**
     * @return string
     */
    public function getFeeType()
    {
        return $this->feeType;
    }
}
