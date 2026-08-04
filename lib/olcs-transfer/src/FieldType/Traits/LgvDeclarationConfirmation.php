<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Lgv Declaration Confirmation
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait LgvDeclarationConfirmation
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"0","1"}})
     * @Transfer\Optional
     */
    protected $lgvDeclarationConfirmation;

    /**
     * @return mixed
     */
    public function getLgvDeclarationConfirmation()
    {
        return $this->lgvDeclarationConfirmation;
    }
}
