<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Variation;

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
     * @param \Dvsa\Olcs\Transfer\Query\Variation\GoodsVehiclesExport $query query
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

        $listQuery = $repo->createPaginatedVehiclesDataForVariationQuery(
            $query,
            $application->getId(),
            $application->getLicence()->getId()
        );

        return $this->getData($listQuery);
    }
}
