<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;

class CanAccessSiWithId extends AbstractHandler implements AuthAwareInterface, RepositoryManagerAwareInterface
{
    use AuthAwareTrait;
    use RepositoryManagerAwareTrait;

    #[\Override]
    public function isValid($dto)
    {
        if (!$this->isInternalUser()) {
            return false;
        }

        if ($dto->getCaseId() === null) {
            return true;
        }

        $si = $this->getRepo('SeriousInfringement')->fetchById($dto->getId());

        return $si->getCase()?->getId() === $dto->getCaseId();
    }
}
