<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

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
    protected $extraRepos = ['Licence'];

    /**
     * Get vehicle data for export
     *
     * @param \Dvsa\Olcs\Transfer\Query\Licence\GoodsVehiclesExport $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Licence\Licence $licence */
        $licence = $this->getRepo('Licence')->fetchUsingId($query);

        /** @var Repository\LicenceVehicle $repo */
        $repo = $this->getRepo();

        $listQuery = $repo->createPaginatedVehiclesDataForLicenceQuery(
            $query,
            $licence->getId()
        );

        return $this->getData($listQuery);
    }
}
