<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity;

/**
 * Role
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Role extends AbstractRepository
{
    protected $entity = Entity\User\Role::class;

    /**
     * Apply List Joins
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with($this->alias . '.rolePermissions', 'rp')
            ->with('rp.permission', 'p');
    }

    /**
     * Fetch by name
     *
     * @param string $role Role
     *
     * @return Entity\User\Role|null
     */
    public function fetchByRole($role)
    {
        return current($this->fetchByX('role', [$role])) ?: null;
    }
}
