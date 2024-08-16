<?php

/**
 * Role List
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Role List
 */
class RoleList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Role';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['rolePermissions' => ['permission']]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
