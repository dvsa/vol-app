<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Drivers
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 */
trait Drivers
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min":0, "max":99})
     */
    protected $drivers;

    /**
     * @return int
     */
    public function getDrivers()
    {
        return $this->drivers;
    }
}
