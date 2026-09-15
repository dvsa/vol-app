<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait PresidingStaffName
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait PresidingStaffNameOptional
{
    /**
     * @var string|null
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    protected $presidingStaffName;

    /**
     * @return string|null
     */
    public function getPresidingStaffName(): ?string
    {
        return $this->presidingStaffName;
    }
}
