<?php

namespace Common\Rbac\Role;

use Common\Service\Cqrs\Query\QuerySender;
use Rbac\Role\Role;
use LmcRbacMvc\Role\RoleProviderInterface;
use Dvsa\Olcs\Transfer\Query\User\RoleList;

/**
 * Class RoleProvider
 * @package Common\Rbac\Role
 */
class RoleProvider implements RoleProviderInterface
{
    /**
     * @var Role[]
     */
    private $roles = [];

    public function __construct(private QuerySender $queryService)
    {
    }

    /**
     * Get the roles from the provider
     *
     * @param  string[] $roleNames
     * @return \Rbac\Role\RoleInterface[]
     */
    #[\Override]
    public function getRoles(array $roleNames)
    {
        if ($this->roles === []) {
            $data = $this->queryService->send(RoleList::create([]));

            if (!$data->isOk()) {
                throw new \RuntimeException('Unable to retrieve roles');
            }

            foreach ($data->getResult()['results'] as $roleData) {
                $role = new Role($roleData['role']);

                if (isset($roleData['rolePermissions'])) {
                    foreach ($roleData['rolePermissions'] as $permission) {
                        $role->addPermission($permission['permission']['name']);
                    }
                }

                $this->roles[$roleData['role']] = $role;
            }
        }

        return array_intersect_key($this->roles, array_combine($roleNames, $roleNames));
    }
}
