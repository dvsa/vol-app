<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait ContinuationDetail
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $continuationDetail;

    /**
     * @return int
     */
    public function getContinuationDetail()
    {
        return $this->continuationDetail;
    }
}
