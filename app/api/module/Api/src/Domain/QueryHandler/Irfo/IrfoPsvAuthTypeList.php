<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoPsvAuthType list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class IrfoPsvAuthTypeList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoPsvAuthType';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
