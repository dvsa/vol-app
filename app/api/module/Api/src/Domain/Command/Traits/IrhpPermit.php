<?php

namespace Dvsa\Olcs\Api\Domain\Command\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * IRHP Permit
 *
 * @package Dvsa\Olcs\Api\Domain\Command\Traits
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
