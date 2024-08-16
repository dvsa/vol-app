<?php

/**
 * Get continuation details list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get continuation details list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['Continuation'];

    public function handleQuery(QueryInterface $query)
    {
        $continuation = $this->getRepo('Continuation')->fetchWithTa($query->getContinuationId());
        $header = [
            'year' => $continuation->getYear(),
            'month' => $continuation->getMonth(),
            'name' => $continuation->getTrafficArea()->getName()
        ];

        $results = $this->getRepo()->fetchDetails(
            $query->getContinuationId(),
            $query->getLicenceStatus(),
            $query->getLicenceNo(),
            $query->getMethod(),
            $query->getStatus()
        );
        return [
            'results' => $this->resultList(
                $results,
                [
                    'continuation',
                    'status',
                    'licence' => [
                        'status',
                        'organisation',
                        'licenceType',
                        'goodsOrPsv'
                    ]
                ]
            ),
            'header' => $header,
            'count' => count($results)
        ];
    }
}
