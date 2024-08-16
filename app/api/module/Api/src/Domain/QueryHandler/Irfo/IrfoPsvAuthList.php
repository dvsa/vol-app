<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Irfo Psv Auth List
 */
final class IrfoPsvAuthList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoPsvAuth';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'irfoPsvAuthType',
                    'status',
                ]
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
