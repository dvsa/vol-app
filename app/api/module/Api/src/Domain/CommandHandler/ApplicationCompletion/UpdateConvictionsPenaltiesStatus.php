<?php

/**
 * Update ConvictionsPenalties Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update ConvictionsPenalties Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateConvictionsPenaltiesStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'ConvictionsPenalties';

    protected function isSectionValid(Application $application)
    {
        if (!in_array($application->getPrevConviction(), ['Y', 'N'])) {
            return false;
        }

        if ($application->getConvictionsConfirmation() !== 'Y') {
            return false;
        }

        if ($application->getPrevConviction() === 'Y' && $application->getPreviousConvictions()->isEmpty()) {
            return false;
        }

        return true;
    }
}
