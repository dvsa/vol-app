<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can manage user
 */
class CanManageUser extends AbstractDoesOwnEntity
{
    protected $repo = 'User';

    /**
     * Is valid
     *
     * @param int|null $entityId Entity id
     *
     * @return bool
     */
    #[\Override]
    public function isValid($entityId)
    {
        if ($this->isInternalUser()) {
            return true;
        }

        return $this->isGranted(
            \Dvsa\Olcs\Api\Entity\User\Permission::CAN_MANAGE_USER_SELFSERVE,
            ($entityId !== null) ? $this->getEntity($entityId) : null
        );
    }
}
