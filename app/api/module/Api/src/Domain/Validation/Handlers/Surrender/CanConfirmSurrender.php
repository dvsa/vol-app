<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

class CanConfirmSurrender extends AbstractHandler
{
    public function isValid($dto): bool
    {
        return $this->canConfirmSurrender($dto->getId());
    }
}
