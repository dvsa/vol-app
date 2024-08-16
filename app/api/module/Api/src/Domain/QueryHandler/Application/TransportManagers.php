<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Query;

/**
 * Transport Managers Query Handler
 * Retreive the application and a list of transport manager applications/licences (including contact details)
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagers extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['TransportManagerApplication', 'TransportManagerLicence'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);

        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication $repoApp */
        $repoApp = $this->getRepo('TransportManagerApplication');
        $tmas = $repoApp->fetchWithContactDetailsByApplication($application->getId());

        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence $repoLic */
        $repoLic = $this->getRepo('TransportManagerLicence');
        $tmls = $repoLic->fetchWithContactDetailsByLicence($application->getLicence()->getId());

        $licenceType = $application->getLicenceType() === null
            ? null
            : ['id' => $application->getLicenceType()->getId()];

        return [
            'id' => $application->getId(),
            'licenceType' => $licenceType,
            'transportManagers' => $tmas,
            'licence' => [
                'tmLicences' => $tmls
            ]
        ];
    }
}
