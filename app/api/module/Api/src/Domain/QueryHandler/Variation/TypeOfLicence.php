<?php

/**
 * Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Variation;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;

/**
 * Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity $entity */
        $application = $this->getRepo()->fetchUsingId($query);
        $licence = $application->getLicence();

        return $this->result(
            $application,
            [],
            [
                'canBecomeSpecialRestricted' => $licence->canBecomeSpecialRestricted(),
                'canUpdateLicenceType' => $this->isGranted(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence),
                'currentLicenceType' => $licence->getLicenceType()->getId(),
                'currentVehicleType' => $licence->getVehicleType()->getId()
            ]
        );
    }
}
