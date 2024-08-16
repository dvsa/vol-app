<?php

/**
 * Psv Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Psv Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvLicenceVehicle extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    public function handleQuery(QueryInterface $query)
    {
        $licenceVehicle = $this->getRepo()->fetchUsingId($query);

        $showHistory = $this->isGranted(Permission::INTERNAL_USER);
        $history = [];
        if ($showHistory) {
            $history = $this->getRepo()->fetchByVehicleId($licenceVehicle->getVehicle()->getId());
        }

        return $this->result(
            $licenceVehicle,
            ['vehicle', 'licence'],
            ['showHistory' => $showHistory, 'history' => $history]
        );
    }
}
