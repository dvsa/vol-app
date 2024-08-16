<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Decision;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Decision as DecisionRepo;

/**
 * Decision List QueryHandler
 */
final class DecisionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Decision';

    public function handleQuery(QueryInterface $query)
    {
        /** @var DecisionRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
