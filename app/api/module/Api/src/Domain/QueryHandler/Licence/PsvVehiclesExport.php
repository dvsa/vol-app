<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Psv vehicles export
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvVehiclesExport extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $vehicles = $this->getRepo()->fetchPsvVehiclesByLicenceId($query->getId(), $query->getIncludeRemoved());
        // this query is recursion save, no need to serialise, already serialised
        return [
            'result' => $vehicles,
            'count' => count($vehicles)
        ];
    }
}
