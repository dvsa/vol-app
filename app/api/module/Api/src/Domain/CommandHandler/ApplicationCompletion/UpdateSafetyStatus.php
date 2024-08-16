<?php

/**
 * Update Safety Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update Safety Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateSafetyStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'Safety';

    protected function isSectionValid(Application $application)
    {
        $licence = $application->getLicence();

        if ((int)$licence->getSafetyInsVehicles() < 1) {
            return false;
        }

        if (!in_array($licence->getSafetyInsVaries(), ['Y', 'N'])) {
            return false;
        }

        if ($licence->getTachographIns() === null) {
            return false;
        }

        if (count($licence->getWorkshops()) < 1) {
            return false;
        }

        if ($application->getSafetyConfirmation() !== 'Y') {
            return false;
        }

        $tachInsName = $licence->getTachographInsName();
        if ($licence->getTachographIns()->getId() === Licence::TACH_EXT && empty($tachInsName)) {
            return false;
        }

        if ($application->canHaveTrailer() && $licence->getSafetyInsTrailers() === null) {
            return false;
        }

        return true;
    }
}
