<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Irfo Gv Permit QueryHandler
 */
final class IrfoGvPermitList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoGvPermit';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'irfoGvPermitType',
                    'irfoPermitStatus',
                ]
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
