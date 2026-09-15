<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Country
 * @author Andy Newton <andy@vitri.ltd>
 */
trait Country
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToUpper")
     * @Transfer\Validator("Laminas\I18n\Validator\Alpha")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":2})
     */
    public $country;

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
