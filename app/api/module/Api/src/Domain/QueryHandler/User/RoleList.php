<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

class RoleList extends AbstractQueryHandler implements ToggleAwareInterface
{
    use ToggleAwareTrait;

    protected $repoServiceName = 'Role';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        $result = $this->resultList(
            $repo->fetchList($query, Query::HYDRATE_OBJECT),
            ['rolePermissions' => ['permission']]
        );

        $count = $repo->fetchCount($query);

        //this can't be easily toggled via the repo, and the query is cached, so for temporary code it's OK here
        if ($this->toggleService->isDisabled(FeatureToggle::TRANSPORT_CONSULTANT_ROLE)) {
            foreach ($result as $key => $role) {
                if ($role['role'] === Role::ROLE_OPERATOR_TC) {
                    $count--;
                    unset($result[$key]);
                    break;
                }
            }
        }

        return [
            'result' => $result,
            'count' => $count,
        ];
    }
}
