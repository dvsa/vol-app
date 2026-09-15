<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait LetterTypeOptional
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait LetterTypeOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $letterType;

    /**
     * Get letter type
     *
     * @return int|null
     */
    public function getLetterType()
    {
        return $this->letterType;
    }
}
