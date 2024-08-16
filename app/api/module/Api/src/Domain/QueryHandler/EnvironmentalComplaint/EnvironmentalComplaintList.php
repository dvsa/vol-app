<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\EnvironmentalComplaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Complaint;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * EnvironmentalComplaint QueryHandler
 */
final class EnvironmentalComplaintList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Complaint';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Complaint $repo */
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
