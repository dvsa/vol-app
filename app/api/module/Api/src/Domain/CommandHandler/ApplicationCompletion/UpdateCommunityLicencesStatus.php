<?php

/**
 * Update CommunityLicences Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update CommunityLicences Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateCommunityLicencesStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'CommunityLicences';

    protected function isSectionValid(Application $application)
    {
        return true;
    }
}
