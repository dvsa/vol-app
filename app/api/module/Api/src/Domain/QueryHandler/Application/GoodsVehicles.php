<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Doctrine\ORM\Query;

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehicles extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['LicenceVehicle'];

    protected $licenceVehicleMethod = 'createPaginatedVehiclesDataForApplicationQuery';

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
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($query);
        $licenceId = $application->getLicence()->getId();

        /** @var \Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle $lvRepo */
        $lvRepo = $this->getRepo('LicenceVehicle');

        $lvQuery = $lvRepo->{$this->licenceVehicleMethod}(
            $query,
            $application->getId(),
            $licenceId
        );

        return $this->result(
            $application,
            [],
            [
                'canReprint' => $this->canReprint($application),
                'canTransfer' => false,
                'canExport' => false,
                'canPrintVehicle' => $this->isGranted(Permission::INTERNAL_USER),
                'licenceVehicles' => [
                    'results' => $this->resultList(
                        $lvRepo->fetchPaginatedList($lvQuery, Query::HYDRATE_OBJECT),
                        [
                            'vehicle',
                            'goodsDiscs',
                            'interimApplication'
                        ]
                    ),
                    'count' => $lvRepo->fetchPaginatedCount($lvQuery)
                ],
                'spacesRemaining' => $application->getRemainingSpaces(),
                'activeVehicleCount' => $application->getActiveLicenceVehiclesCount(),
                'allVehicleCount' => $lvRepo->fetchAllVehiclesCount($licenceId),
            ]
        );
    }

    /**
     * Can reprint
     *
     * @param ApplicationEntity $application application
     *
     * @return bool
     */
    private function canReprint(ApplicationEntity $application)
    {
        return $application->getStatus()->getId() !== ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED;
    }
}
