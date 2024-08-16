<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehicles extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['LicenceVehicle'];

    /**
     * Handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        /** @var Repository\LicenceVehicle $licVehicleRepo */
        $licVehicleRepo = $this->getRepo('LicenceVehicle');

        $lvQuery = $licVehicleRepo->createPaginatedVehiclesDataForLicenceQuery(
            $query,
            $licence->getId()
        );

        return $this->result(
            $licence,
            [
                'organisation'
            ],
            [
                'canReprint' => true,
                'canTransfer' => $this->canTransfer($licence),
                'canExport' => true,
                'canPrintVehicle' => $this->isGranted(Permission::INTERNAL_USER),
                'licenceVehicles' => [
                    'results' => $this->resultList(
                        $licVehicleRepo->fetchPaginatedList($lvQuery, Query::HYDRATE_OBJECT),
                        [
                            'vehicle',
                            'goodsDiscs',
                            'interimApplication'
                        ]
                    ),
                    'count' => $licVehicleRepo->fetchPaginatedCount($lvQuery)
                ],
                'spacesRemaining' => $licence->getRemainingSpaces(),
                'activeVehicleCount' => $licence->getActiveVehiclesCount(),
                'allVehicleCount' => $licVehicleRepo->fetchAllVehiclesCount($licence->getId())
            ]
        );
    }

    /**
     * Define, can it be transfered
     *
     * @param LicenceEntity $licence Licence
     *
     * @return bool
     */
    private function canTransfer(LicenceEntity $licence)
    {
        return $licence->getOtherActiveLicences()->count() > 0;
    }
}
