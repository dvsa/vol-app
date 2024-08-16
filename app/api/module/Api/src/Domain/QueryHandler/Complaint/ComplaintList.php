<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Complaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Complaint QueryHandler
 */
final class ComplaintList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Complaint';

    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Complaint $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'case',
                    'complainantContactDetails' => [
                        'person'
                    ],
                    'operatingCentres' => [
                        'address'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
