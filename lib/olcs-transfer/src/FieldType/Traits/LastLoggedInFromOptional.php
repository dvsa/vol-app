<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait LastLoggedInFromOptional
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $lastLoggedInFrom;

    /**
     * @return string
     */
    public function getLastLoggedInFrom()
    {
        return $this->lastLoggedInFrom;
    }
}
