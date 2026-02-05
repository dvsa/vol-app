<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Team;

use Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\Team\TeamList as TeamListQry;

class TeamList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Team';

    /**
     * handle list query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof TeamListQry);
        $userInfo = $this->getUserData();

        //we know the user will always be internal, most won't be allowed to access all data
        if (!$userInfo['dataAccess']['canAccessAll']) {
            $queryData = $query->getArrayCopy();
            $queryData['trafficAreas'] = $userInfo['dataAccess']['trafficAreas'];
            $queryWithTa = TeamListByTrafficArea::create($queryData);

            return $this->getQueryHandler()->handleQuery($queryWithTa);
        }

        return parent::handleQuery($query);
    }
}
