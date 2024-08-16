<?php

/**
 * Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LicenceVehicle;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceVehicle extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    protected $extraRepos = ['VehicleHistoryView'];

    public function handleQuery(QueryInterface $query)
    {
        $licenceVehicle = $this->getRepo()->fetchUsingId($query);

        $showHistory = $this->isGranted(Permission::INTERNAL_USER);

        $history = [];

        if ($showHistory) {
            $history = $this->getRepo('VehicleHistoryView')->fetchByVrm($licenceVehicle->getVehicle()->getVrm());
        }

        return $this->result(
            $licenceVehicle,
            [
                'vehicle',
                'goodsDiscs'
            ],
            [
                'showHistory' => $showHistory,
                'history' => $history
            ]
        );
    }
}
