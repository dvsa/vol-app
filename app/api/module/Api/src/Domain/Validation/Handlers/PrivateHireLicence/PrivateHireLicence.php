<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create / Update private hire licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrivateHireLicence extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Is valid
     *
     * @param CommandInterface $dto dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        if ($this->isInternalUser()) {
            return true;
        }
        return $this->canAccessLicence($dto->getLicence());
    }
}
