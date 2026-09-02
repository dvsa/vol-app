<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;

class IsSystemUserOrSystemAdmin extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    #[\Override]
    public function isValid($dto)
    {
        return $this->isSystemUser()
            || $this->isGranted(Permission::SYSTEM_ADMIN);
    }
}
