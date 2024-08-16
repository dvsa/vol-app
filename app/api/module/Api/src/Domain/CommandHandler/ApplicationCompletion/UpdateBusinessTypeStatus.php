<?php

/**
 * Update BusinessType Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update BusinessType Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateBusinessTypeStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'BusinessType';

    protected function isSectionValid(Application $application)
    {
        return $application->getLicence()->getOrganisation()->getType() !== null;
    }
}
