<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * IRHP Permit
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait IrhpPermit
{
    /**
     * @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $irhpPermit;

    /**
     * @return int
     */
    public function getIrhpPermit(): int
    {
        return $this->irhpPermit;
    }
}
