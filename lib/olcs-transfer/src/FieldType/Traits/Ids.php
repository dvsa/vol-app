<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Ids
 */
trait Ids
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
