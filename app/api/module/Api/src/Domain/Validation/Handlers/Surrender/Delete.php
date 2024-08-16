<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

class Delete extends AbstractHandler
{
    public function isValid($dto): bool
    {
        if (!$this->canAccessLicence($dto->getId())) {
            return false;
        }

        return $this->canDeleteSurrender($dto->getId());
    }
}
