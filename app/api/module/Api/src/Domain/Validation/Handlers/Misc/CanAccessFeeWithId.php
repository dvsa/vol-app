<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;

/**
 * Can Access Fee With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessFeeWithId extends AbstractHandler implements AuthAwareInterface, RepositoryManagerAwareInterface
{
    use AuthAwareTrait;
    use RepositoryManagerAwareTrait;

    /**
     * @inheritdoc
     */
    #[\Override]
    public function isValid($dto)
    {
        if (!$this->isInternalUser() && !$this->canAccessFee($this->getId($dto))) {
            return false;
        }

        $fee = $this->getEntity($dto);

        if ($dto->getLicenceId() !== null) {
            return $fee->getLicence()?->getId() === $dto->getLicenceId();
        }

        if ($dto->getApplicationId() !== null) {
            return $fee->getApplication()?->getId() === $dto->getApplicationId();
        }

        return true;
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }

    protected function getEntity($dto)
    {
        return $this->getRepo('Fee')->fetchById($this->getId($dto));
    }
}
