<?php

/**
 * Update VehiclesPsv Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;

/**
 * Update VehiclesPsv Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateVehiclesPsvStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'VehiclesPsv';

    protected function isSectionValid(Application $application)
    {
        if ($application->getHasEnteredReg() === 'N') {
            return true;
        }

        if (empty($application->getLicence()->getLicenceVehicles())) {
            return false;
        }

        if ($application->getTotAuthVehicles() === null) {
            return false;
        }

        return true;
    }
}
