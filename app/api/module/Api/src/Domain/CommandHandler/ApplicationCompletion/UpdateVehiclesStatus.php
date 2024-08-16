<?php

/**
 * Update Vehicles Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update Vehicles Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateVehiclesStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'Vehicles';

    protected function isSectionValid(Application $application)
    {
        if ($application->getHasEnteredReg() === 'N') {
            return true;
        }

        $vehicles = $application->getActiveVehicles();

        if (count($vehicles) === 0) {
            return false;
        }

        $totalAuth = (int)$application->getTotAuthVehicles();

        if (count($vehicles) > $totalAuth) {
            return false;
        }

        return true;
    }
}
