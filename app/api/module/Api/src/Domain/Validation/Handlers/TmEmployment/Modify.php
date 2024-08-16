<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\TmEmployment;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Modify TmEmployment
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Modify extends AbstractHandler implements AuthAwareInterface
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

        return $this->canAccessTmEmployment($dto->getId());
    }
}
