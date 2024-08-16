<?php

/**
 * Get a list of Users
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\UserList as UserListQry;
use Dvsa\Olcs\Api\Domain\Query\User\UserListByTrafficArea;
use Doctrine\ORM\Query;

/**
 * Get a list of Users
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UserList extends AbstractQueryHandler
{
    protected $repoServiceName = 'User';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof UserListQry);

        $userInfo = $this->getUserData();
        $queryData = $query->getArrayCopy();

        // if the user is internal and can't access all data filter list by ta,
        // Unless the request has an operator id, in which case all users for org are needed.
        if (
            $userInfo['isInternal']
            && !$userInfo['dataAccess']['canAccessAll']
            && empty($queryData['organisation'])
        ) {
            $queryData['trafficAreas'] = $userInfo['dataAccess']['trafficAreas'];
            $queryWithTa = UserListByTrafficArea::create($queryData);

            return $this->getQueryHandler()->handleQuery($queryWithTa);
        }

        /** @var User $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList(
                    $query,
                    Query::HYDRATE_OBJECT
                ),
                [
                    'contactDetails' => ['person'],
                    'roles'
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
