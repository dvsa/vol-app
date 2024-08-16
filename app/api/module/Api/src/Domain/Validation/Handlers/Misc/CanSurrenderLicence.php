<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

class CanSurrenderLicence extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public function isValid($dto): bool
    {
        return $this->canAccessLicence($dto->getId()) && $this->isLicenceSurrenderable($dto->getId());
    }
}
