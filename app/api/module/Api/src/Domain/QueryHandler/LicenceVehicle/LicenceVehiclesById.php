<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class LicenceVehiclesById extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById|QueryInterface $query
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $licenceVehicles = $this->getRepo()->fetchByIds($query->getIds());

        $result = $this->resultList(
            $licenceVehicles,
            ['vehicle']
        );

        return [
            'results' => $result,
            'count' => count($result)
        ];
    }
}
