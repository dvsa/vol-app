<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Countries
 */
trait Countries
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayValidator("Laminas\Validator\NotEmpty")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":2})
     */
    protected $countries = [];

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }
}
