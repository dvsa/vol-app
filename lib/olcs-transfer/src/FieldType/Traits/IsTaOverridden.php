<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IsTaOveridden
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait IsTaOverridden
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y","N"}})
     */
    protected $taIsOverridden;

    /**
     * @return string
     */
    public function getTaIsOverridden(): ?string
    {
        return $this->taIsOverridden;
    }
}
