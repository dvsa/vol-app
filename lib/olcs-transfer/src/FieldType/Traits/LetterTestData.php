<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Letter Test Data
 */
trait LetterTestData
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $letterTestData;

    /**
     * @return int
     */
    public function getLetterTestData()
    {
        return $this->letterTestData;
    }
}
