<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\Vehicles as VehiclesQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Vehicles extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * @param VehiclesQuery $query
     */
    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();
        $lvQuery = $repo->createPaginatedVehiclesDataForLicenceQuery(
            $query,
            $query->getId()
        );

        $licenceVehicles = $this->resultList(
            $repo->fetchPaginatedList($lvQuery, Query::HYDRATE_OBJECT),
            [
                'vehicle',
                'goodsDiscs'
            ]
        );

        return [
            'results' => $licenceVehicles,
            'count' => $repo->fetchPaginatedCount($lvQuery)
        ];
    }
}
