<?php

namespace Dvsa\Olcs\Api\Rbac;

use Dvsa\Olcs\Api\Entity\User\User;
use LmcRbacMvc\Identity\IdentityInterface;

/**
 * Identity
 */
class Identity implements IdentityInterface
{
    /**
     * @var array
     */
    private $roles;

    public function __construct(private readonly User $user)
    {
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the list of roles of this identity
     *
     * @return string[]|\Rbac\Role\RoleInterface[]
     */
    public function getRoles()
    {
        if ($this->roles === null) {
            $this->roles = [];
            $userRoles = $this->user->getRoles();

            foreach ($userRoles as $userRole) {
                $this->roles[] = $userRole->getRole();
            }
        }

        return $this->roles;
    }
}
