<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of Transport Manager Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerLicence';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $query \Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetList */

        /* @var $repo \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'licence' => [
                        'status',
                        'licenceType',
                    ],
                    'transportManager' => [
                        'homeCd' => [
                            'person',
                        ],
                        'tmType',
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
