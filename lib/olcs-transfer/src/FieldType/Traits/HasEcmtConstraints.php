<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait HasEcmtConstraints
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
trait HasEcmtConstraints
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $hasEcmtConstraints;

    /**
     * @return int
     */
    public function hasEcmtConstraints()
    {
        return $this->hasEcmtConstraints;
    }
}
