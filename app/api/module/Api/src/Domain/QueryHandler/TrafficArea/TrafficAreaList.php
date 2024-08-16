<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TrafficArea;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaInternalList;

class TrafficAreaList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TrafficArea';

    public function handleQuery(QueryInterface $query)
    {
        $userInfo = $this->getUserData();

        if ($userInfo['isInternal']) {
            $queryData = [
                'trafficAreas' => $userInfo['dataAccess']['trafficAreas']
            ];
            $query = TrafficAreaInternalList::create($queryData);
            return $this->getQueryHandler()->handleQuery($query);
        }

        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
