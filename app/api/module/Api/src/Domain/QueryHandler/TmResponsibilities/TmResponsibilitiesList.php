<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmResponsibilities;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of Transport Manager Licences and Transport Manager Applications
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmResponsibilitiesList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerLicence';

    protected $extraRepos = ['TransportManagerApplication'];

    /**
     * Handle Query
     *
     * @param \Dvsa\Olcs\Transfer\Query\TmResponsibilities\TmResponsibilitiesList $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence $repoTmLic */
        $repoTmLic = $this->getRepo();
        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication $repoTmApp */
        $repoTmApp = $this->getRepo('TransportManagerApplication');

        $tmLicences = $repoTmLic->fetchForTransportManager(
            $query->getTransportManager(),
            [
                Licence::LICENCE_STATUS_VALID,
                Licence::LICENCE_STATUS_SUSPENDED,
                Licence::LICENCE_STATUS_CURTAILED,
                Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            ]
        );

        $tmApplications = $repoTmApp->fetchForTransportManager(
            $query->getTransportManager(),
            [
                Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
                Application::APPLICATION_STATUS_NOT_SUBMITTED,
                Application::APPLICATION_STATUS_GRANTED
            ],
            true
        );

        return [
            'result' => $this->resultList(
                $tmLicences,
                [
                    'tmType',
                    'licence' => [
                        'organisation',
                        'status'
                    ],
                    'transportManager',
                ]
            ),
            'count' => count($tmLicences),
            'tmApplications' => $this->resultList(
                $tmApplications,
                [
                    'tmType',
                    'application' => [
                        'licence' => [
                            'organisation'
                        ],
                        'status'
                    ],
                    'transportManager',
                    'tmApplicationStatus',
                ]
            ),
            'tmApplicationsCount' => count($tmApplications)
        ];
    }
}
