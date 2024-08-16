<?php

/**
 * Update OperatingCentres Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update OperatingCentres Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateOperatingCentresStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'OperatingCentres';

    protected function isSectionValid(Application $application)
    {
        if ($application->mustHaveOperatingCentre() && count($application->getOperatingCentres()) === 0) {
            return false;
        }

        if ($application->getTotAuthVehicles() === null) {
            return false;
        }

        if ($application->canHaveTrailer() && $application->getTotAuthTrailers() === null) {
            return false;
        }

        return true;
    }
}
