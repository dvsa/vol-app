<?php

/**
 * Update Type Of Licence Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update Type Of Licence Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTypeOfLicenceStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'TypeOfLicence';

    protected function isSectionValid(Application $application)
    {
        if (!in_array($application->getNiFlag(), ['Y', 'N'])) {
            return false;
        }

        if ($application->getGoodsOrPsv() === null) {
            return false;
        }

        if ($application->getLicenceType() === null) {
            return false;
        }

        return $application->isValidTol(
            $application->getNiFlag(),
            $application->getGoodsOrPsv(),
            $application->getLicenceType(),
            $application->getVehicleType(),
            $application->getLgvDeclarationConfirmation()
        );
    }
}
