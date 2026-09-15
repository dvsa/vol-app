<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is Full Day
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait IsFullDay
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y","N","not-set"}})
     */
    protected $isFullDay;

    /**
     * @return string
     */
    public function getIsFullDay()
    {
        return $this->isFullDay;
    }
}
