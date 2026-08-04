<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait LicenceOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait LicenceOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * Get licence
     *
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set licence
     *
     * @param int $licenceId licence id
     *
     * @return void
     */
    public function setLicence($licenceId)
    {
        $this->licence = (int) $licenceId;
    }
}
