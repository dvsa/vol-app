<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait IsEcmtState
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
trait IsEcmtStateOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $isEcmtState;

    /**
     * @return int
     */
    public function getIsEcmtState()
    {
        return $this->isEcmtState;
    }
}
