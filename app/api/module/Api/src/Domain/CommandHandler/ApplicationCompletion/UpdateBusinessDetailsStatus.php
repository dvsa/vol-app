<?php

/**
 * Update BusinessDetails Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Update BusinessDetails Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateBusinessDetailsStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'BusinessDetails';

    protected function isSectionValid(Application $application)
    {
        $organisation = $application->getLicence()->getOrganisation();

        if ($organisation->getType() === null) {
            return false;
        }

        $namedCo = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY,
            Organisation::ORG_TYPE_LLP,
            Organisation::ORG_TYPE_PARTNERSHIP,
            Organisation::ORG_TYPE_OTHER
        ];

        if (in_array($organisation->getType()->getId(), $namedCo) && $organisation->getName() === null) {
            return false;
        }

        $limitedCo = [Organisation::ORG_TYPE_REGISTERED_COMPANY, Organisation::ORG_TYPE_LLP];

        if (in_array($organisation->getType()->getId(), $limitedCo)) {
            if ($organisation->getCompanyOrLlpNo() === null) {
                return false;
            }

            if ($organisation->getContactDetails() === null) {
                return false;
            }
        }

        return true;
    }
}
