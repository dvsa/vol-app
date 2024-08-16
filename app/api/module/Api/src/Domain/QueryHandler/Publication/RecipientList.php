<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Publication;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Recipient List
 */
final class RecipientList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Recipient';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query)
        ];
    }
}
