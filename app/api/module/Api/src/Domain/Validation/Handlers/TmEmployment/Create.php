<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TmEmployment;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Create extends AbstractHandler implements AuthAwareInterface
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
        if (!empty($dto->getTmaId())) {
            return $this->canAccessTransportManagerApplication($dto->getTmaId());
        }
        if (!empty($dto->getTransportManager())) {
            return $this->canAccessTransportManager($dto->getTransportManager());
        }

        return false;
    }
}
