<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\User\UserPasswordReset as UserPasswordResetEntity;

class UserPasswordReset extends AbstractRepository
{
    protected $entity = UserPasswordResetEntity::class;
}
