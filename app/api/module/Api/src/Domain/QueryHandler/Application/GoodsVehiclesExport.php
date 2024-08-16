<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Lva\AbstractGoodsVehiclesExport;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Goods Vehicles
 *
 * @author Dmitrij Golubev <dmitrij.golubev@valtech.co.uk>
 */
class GoodsVehiclesExport extends AbstractGoodsVehiclesExport
{
    protected $extraRepos = ['Application'];

    /**
     * Get vehicle data for export
     *
     * @param \Dvsa\Olcs\Transfer\Query\Application\GoodsVehiclesExport $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Application\Application $application */
        $application = $this->getRepo('Application')->fetchUsingId($query);

        /** @var Repository\LicenceVehicle $repo */
        $repo = $this->getRepo();

        $listQb = $repo->createPaginatedVehiclesDataForApplicationQuery(
            $query,
            $application->getId(),
            $application->getLicence()->getId()
        );

        return $this->getData($listQb);
    }
}
