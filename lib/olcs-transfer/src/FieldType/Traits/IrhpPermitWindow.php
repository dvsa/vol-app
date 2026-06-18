<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IRHP Permit Window
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IrhpPermitWindow
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irhpPermitWindow;

    /**
     * @return int
     */
    public function getIrhpPermitWindow(): int
    {
        return $this->irhpPermitWindow;
    }
}
