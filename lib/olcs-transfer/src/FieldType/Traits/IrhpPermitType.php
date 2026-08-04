<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * IRHP Permit Type Trait
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitType
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Between",
     *      options={
     *          "min": 0,
     *          "max": 99999
     *      }
     * )
     */
    protected $irhpPermitType;

    /**
     * @return int
     */
    public function getIrhpPermitType(): int
    {
        return $this->irhpPermitType;
    }
}
