<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Letter Type
 */
trait LetterType
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $letterType;

    /**
     * @return int
     */
    public function getLetterType()
    {
        return $this->letterType;
    }
}
