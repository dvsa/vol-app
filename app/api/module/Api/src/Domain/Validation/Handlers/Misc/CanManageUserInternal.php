<?php

/**
 * Can manage user internal
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Can manage user internal
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanManageUserInternal extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->isInternalUser() && $this->isGranted(Permission::CAN_MANAGE_USER_INTERNAL);
    }
}
