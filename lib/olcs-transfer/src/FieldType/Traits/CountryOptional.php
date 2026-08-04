<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Country
 * @author Andy Newton <andy@vitri.ltd>
 */
trait CountryOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     *
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":2})
     */
    public $country;

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }
}
