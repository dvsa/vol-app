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

    #[\Override]
    public function isValid($dto)
    {
        $licence = $dto->getLicence();
        if ($licence !== null && !$this->feeBelongsToLicence($this->getId($dto), $licence)) {
            return false;
        }

        $application = $dto->getApplication();
        if ($application !== null && !$this->feeBelongsToApplication($this->getId($dto), $application)) {
            return false;
        }

        if (!$this->isInternalUser() && !$this->canAccessFee($this->getId($dto))) {
            return false;
        }

        return true;
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
