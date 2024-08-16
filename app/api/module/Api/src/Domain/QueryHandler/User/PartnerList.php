<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Partner List
 */
final class PartnerList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Partner';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT),
                ['address']
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
