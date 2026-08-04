<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SurrenderIdOptional
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait SurrenderLicenceOptional
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
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return  void
     */
    public function setLicence(int $licence): void
    {
        $this->licence = $licence;
    }
}
