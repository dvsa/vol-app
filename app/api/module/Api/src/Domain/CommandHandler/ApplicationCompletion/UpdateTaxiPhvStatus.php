<?php

/**
 * Update TaxiPhv Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update TaxiPhv Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTaxiPhvStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'TaxiPhv';

    protected function isSectionValid(Application $application)
    {
        return !$application->getLicence()->getPrivateHireLicences()->isEmpty() &&
            $application->getLicence()->getTrafficArea() !== null;
    }
}
