<?php

namespace Dvsa\Olcs\Api\Domain\Command\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * IRHP Permit Window
 *
 * @package Dvsa\Olcs\Api\Domain\Command\Traits
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IrhpPermitWindow
{
    /**
     * @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $irhpPermitWindow;

    /**
     * @return int
     */
    public function getIrhpPermitWindow(): int
    {
        return $this->irhpPermitWindow;
    }
}
