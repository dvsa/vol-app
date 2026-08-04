<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Publication Types
 */
trait PubTypeOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"All","A&D","N&P"}})
     */
    protected $pubType = null;

    /**
     * @return string
     */
    public function getPubType()
    {
        return $this->pubType;
    }
}
