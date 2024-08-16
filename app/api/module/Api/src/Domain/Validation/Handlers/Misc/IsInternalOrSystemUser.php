<?php

/**
 * Is Internal or System User
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Is Internal or System User
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IsInternalOrSystemUser extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Is Internal or System User
     *
     * @param CommandInterface|QueryInterface $dto Dto
     *
     * @return boolean
     */
    public function isValid($dto)
    {
        return $this->isInternalUser() || $this->isSystemUser();
    }
}
