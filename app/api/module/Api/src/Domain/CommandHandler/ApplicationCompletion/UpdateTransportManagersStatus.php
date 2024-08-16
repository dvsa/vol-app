<?php

/**
 * Update TransportManagers Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Update TransportManagers Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTransportManagersStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'TransportManagers';

    protected function isSectionValid(Application $application)
    {
        $requiredTransportManager = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        if (
            in_array($application->getLicenceType()->getId(), $requiredTransportManager)
            && $application->getTransportManagers()->isEmpty()
        ) {
            return false;
        }

        return true;
    }
}
