<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Query;

/**
 * Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehicles extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['LicenceVehicle'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence Entity\Licence\Licence */
        $licence = $this->getRepo()->fetchUsingId($query);
        $lvQuery = $this->getRepo('LicenceVehicle')->createPaginatedVehiclesDataForLicenceQueryPsv(
            $query,
            $licence->getId()
        );

        $flags = [
            'canTransfer'        => !$licence->getOtherActiveLicences()->isEmpty(),
            'hasBreakdown'       => (int) $licence->getTotAuthVehicles() > 0,
            'activeVehicleCount' => $licence->getActiveVehiclesCount(),
            'allVehicleCount'    => $licence->getLicenceVehicles()->count(),
            'licenceVehicles'    => [
                'results' => $this->resultList(
                    $this->getRepo('LicenceVehicle')->fetchPaginatedList($lvQuery, Query::HYDRATE_OBJECT),
                    [
                        'vehicle',
                    ]
                ),
                'count' => $this->getRepo('LicenceVehicle')->fetchPaginatedCount($lvQuery)
            ]
        ];

        return $this->result(
            $licence,
            [
                'organisation',
            ],
            $flags
        );
    }
}
