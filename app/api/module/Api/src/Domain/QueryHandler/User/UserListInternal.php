<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Qry;
use Dvsa\Olcs\Api\Domain\Query\User\UserListInternalByTrafficArea;
use Doctrine\ORM\Query;

class UserListInternal extends AbstractQueryHandler
{
    protected $repoServiceName = 'User';

    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof Qry);

        $userInfo = $this->getUserData();

        //we know the user will always be internal, most won't be allowed to access all data
        if (!$userInfo['dataAccess']['canAccessAll']) {
            $queryData = $query->getArrayCopy();
            $queryData['trafficAreas'] = $userInfo['dataAccess']['trafficAreas'];
            $queryWithTa = UserListInternalByTrafficArea::create($queryData);

            return $this->getQueryHandler()->handleQuery($queryWithTa);
        }

        $repo = $this->getRepo();
        assert($repo instanceof UserRepo);

        return [
            'result' => $this->resultList(
                $repo->fetchList(
                    $query,
                    Query::HYDRATE_OBJECT
                ),
                [
                    'team',
                    'contactDetails' => [
                        'person'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
