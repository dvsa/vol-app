<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Surrender;

trait SurrenderStatusAwareTrait
{
    /**
     * @param array $surrender
     *
     * @return bool
     */
    protected function hasBeenSigned(Surrender $surrender)
    {
        return $surrender->getStatus()->getId() === Surrender::SURRENDER_STATUS_SIGNED;
    }
}
