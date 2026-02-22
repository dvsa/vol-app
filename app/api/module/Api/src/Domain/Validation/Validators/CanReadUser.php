<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can read user record
 */
class CanReadUser extends AbstractDoesOwnEntity
{
    protected $repo = 'User';

    /**
     * Is valid
     *
     * @param int $entityId Entity id
     *
     * @return bool
     */
    #[\Override]
    public function isValid($entityId)
    {
        if ($this->isInternalUser()) {
            return true;
        }

        if ($this->canManageUser($entityId)) {
            return true;
        }

        return $this->isGranted(
            \Dvsa\Olcs\Api\Entity\User\Permission::CAN_READ_USER_SELFSERVE,
            $this->getEntity($entityId)
        );
    }
}
