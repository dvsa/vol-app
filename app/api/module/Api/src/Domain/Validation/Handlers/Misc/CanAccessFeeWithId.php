<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Can Access Fee With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessFeeWithId extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    #[\Override]
    public function isValid($dto)
    {
        if (!$this->isInternalUser() && !$this->canAccessFee($this->getId($dto))) {
            return false;
        }

        if ($dto->getLicenceId() !== null) {
            return $this->feeBelongsToLicence($this->getId($dto), $dto->getLicenceId());
        }

        if ($dto->getApplicationId() !== null) {
            return $this->feeBelongsToApplication($this->getId($dto), $dto->getApplicationId());
        }

        return true;
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
