<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRFO PSV Auth Continuation List
 */
final class IrfoPsvAuthContinuationList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoPsvAuth';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['organisation', 'irfoPsvAuthType']
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
