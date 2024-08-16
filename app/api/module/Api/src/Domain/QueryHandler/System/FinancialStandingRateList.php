<?php

/**
 * Financial Standing Rate List
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * Financial Standing Rate List
 */
class FinancialStandingRateList extends AbstractQueryHandler
{
    protected $repoServiceName = 'FinancialStandingRate';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Repo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
