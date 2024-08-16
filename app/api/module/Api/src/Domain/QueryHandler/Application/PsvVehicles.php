<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

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
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['LicenceVehicle'];

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /* @var $application Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);
        $licenceId = $application->getLicence()->getId();

        /** @var \Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle $lvRepo */
        $lvRepo = $this->getRepo('LicenceVehicle');

        $lvQuery = $lvRepo->createPaginatedVehiclesDataForApplicationQueryPsv(
            $query,
            $application->getId(),
            $licenceId
        );

        $flags = [
            'canTransfer'        => false,
            'hasBreakdown'       => (int) $application->getTotAuthVehicles() > 0,
            'activeVehicleCount' => $application->getActiveLicenceVehiclesCount(),
            'allVehicleCount'    => $lvRepo->fetchAllVehiclesCount($licenceId),
            'licenceVehicles'    => [
                'results' => $this->resultList(
                    $lvRepo->fetchPaginatedList($lvQuery, Query::HYDRATE_OBJECT),
                    [
                        'vehicle'
                    ]
                ),
                'count' => $lvRepo->fetchPaginatedCount($lvQuery)
            ]
        ];

        return $this->result($application, [], $flags);
    }
}
