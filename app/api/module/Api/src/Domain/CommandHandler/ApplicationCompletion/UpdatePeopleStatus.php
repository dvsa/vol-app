<?php

/**
 * Update People Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Update People Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdatePeopleStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'People';

    protected function isSectionValid(Application $application)
    {
        $num = count($application->getLicence()->getOrganisation()->getOrganisationPersons());

        $applicationOrganisationPersons = $application->getApplicationOrganisationPersons();
        foreach ($applicationOrganisationPersons as $aop) {
            if ($aop->getAction() === 'A') {
                $num++;
            }
            if ($aop->getAction() === 'D') {
                $num--;
            }
        }

        return ($num > 0);
    }
}
