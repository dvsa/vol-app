<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is Cancelled
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait IsCancelled
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isCancelled;

    /**
     * @return string
     */
    public function getIsCancelled()
    {
        return $this->isCancelled;
    }
}
