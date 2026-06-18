<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IrhpApplicationOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 *
 */
trait IrhpApplicationOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irhpApplication;

    /**
     * Get irhpApplication
     *
     * @return int
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set irhpApplication
     *
     * @param $irhpApplicationId
     * @return void
     */
    public function setIrhpApplication($irhpApplicationId)
    {
        $this->irhpApplication = (int) $irhpApplicationId;
    }
}
