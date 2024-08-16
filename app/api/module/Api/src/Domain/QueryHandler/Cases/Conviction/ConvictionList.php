<?php

/**
 * ConvictionList
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Conviction as ConvictionRepository;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * ConvictionList
 */
class ConvictionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Conviction';

    public function handleQuery(QueryInterface $query)
    {
        /** @var ConvictionRepository $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT), ['case']),
            'count' => $repo->fetchCount($query)
        ];
    }
}
