<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

class CanAccessSiWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    #[\Override]
    public function isValid($dto)
    {
        if ($dto->getCase() !== null && !$this->seriousInfringementBelongsToCase($dto->getId(), $dto->getCase())) {
            return false;
        }

        if (!$this->isInternalUser()) {
            return false;
        }

        return true;
    }
}
