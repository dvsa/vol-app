<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;

/**
 * List of pending publications
 */
final class PendingList extends AbstractQueryHandler
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Publication';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var PublicationRepo $repo
         */
        $repo = $this->getRepo();
        $result = $repo->fetchPendingList($query);

        return [
            'result' => $this->resultList(
                $result['results'],
                [
                    'pubStatus',
                    'trafficArea',
                    'document'
                ]
            ),
            'count' => $result['count']
        ];
    }
}
