<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ConditionUndertaking;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;

/**
 * ConditionUndertakingList QueryHandler
 */
final class ConditionUndertakingList extends AbstractQueryHandler
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleQuery(QueryInterface $query)
    {
        /** @var ConditionUndertakingRepo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'operatingCentre' => [
                        'address'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
