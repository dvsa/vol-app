<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Is Internal Edit or System User
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IsInternalEditOrSystemUser extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Is Internal Edit or System User
     *
     * @param CommandInterface|QueryInterface $dto Dto
     *
     * @return boolean
     */
    public function isValid($dto)
    {
        return $this->isGranted(Permission::INTERNAL_EDIT) || $this->isSystemUser();
    }
}
