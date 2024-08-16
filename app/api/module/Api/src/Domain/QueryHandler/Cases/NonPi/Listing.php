<?php

/**
 * NonPi Listing
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\NonPi as NonPiRepository;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * Listing
 */
class Listing extends AbstractQueryHandler
{
    protected $repoServiceName = 'NonPi';

    public function handleQuery(QueryInterface $query)
    {
        /** @var NonPiRepository $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query)
        ];
    }
}
