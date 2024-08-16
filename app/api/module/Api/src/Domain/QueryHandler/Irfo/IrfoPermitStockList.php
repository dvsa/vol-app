<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoPermitStock QueryHandler
 */
final class IrfoPermitStockList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoPermitStock';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'irfoGvPermit' => [
                        'organisation'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
