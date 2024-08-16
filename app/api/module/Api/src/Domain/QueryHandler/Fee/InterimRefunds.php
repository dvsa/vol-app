<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class InterimRefunds extends AbstractQueryHandler
{
    protected $repoServiceName = 'Fee';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var $query \Dvsa\Olcs\Transfer\Query\Fee\InterimRefunds
         */

        /**
         * @var  $repo \Dvsa\Olcs\Api\Domain\Repository\Fee
         */
        $repo = $this->getRepo();

        $query = $this->modifyTrafficAreaQueryBasedOnUser($query);

        $after = $query->getStartDate();
        $before = $query->getEndDate();
        $sort = $query->getSort();
        $order = $query->getOrder();
        $trafficArea = $query->getTrafficAreas();

        $refunds = $repo->fetchInterimRefunds($after, $before, $sort, $order, $trafficArea);

        return [
            'count' => count($refunds),
            'results' => $this->resultList($refunds, [
                'licence' =>
                    [
                        'status',
                        'organisation'
                    ],
                'feeTransactions'
            ])
        ];
    }
}
