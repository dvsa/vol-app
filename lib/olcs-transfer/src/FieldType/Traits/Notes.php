<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Notes Trait
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Notes
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    protected $notes;

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }
}
