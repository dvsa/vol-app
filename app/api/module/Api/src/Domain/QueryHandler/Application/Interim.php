<?php

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Interim extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $canSetStatusMap = [
        ApplicationEntity::INTERIM_STATUS_INFORCE,
        ApplicationEntity::INTERIM_STATUS_REFUSED,
        ApplicationEntity::INTERIM_STATUS_REVOKED,
        ApplicationEntity::INTERIM_STATUS_GRANTED,
        ApplicationEntity::INTERIM_STATUS_ENDED
    ];

    protected $restrictedUpdateMap = [
        ApplicationEntity::INTERIM_STATUS_REFUSED,
        ApplicationEntity::INTERIM_STATUS_REVOKED
    ];

    public function handleQuery(QueryInterface $query)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $addedOrUpdated = Criteria::create();
        $addedOrUpdated->andWhere(
            $addedOrUpdated->expr()->in('action', ['A', 'U'])
        );

        $notRemoved = Criteria::create();
        $notRemoved->andWhere(
            $notRemoved->expr()->isNull('removalDate')
        );

        $interimStatus = null;
        if ($application->getInterimStatus() !== null) {
            $interimStatus = $application->getInterimStatus()->getId();
        }

        return $this->result(
            $application,
            [
                'operatingCentres' => [
                    'operatingCentre' => [
                        'address'
                    ],
                    'criteria' => $addedOrUpdated
                ],
                'licenceVehicles' => [
                    'vehicle',
                    'interimApplication',
                    'goodsDiscs',
                    'criteria' => $notRemoved
                ],
                'interimStatus',
                'licence' => [
                    'communityLics' => [
                        'status'
                    ]
                ]
            ],
            [
                'isInterimRequested' => $interimStatus === ApplicationEntity::INTERIM_STATUS_REQUESTED,
                'isInterimInforce' => $interimStatus === ApplicationEntity::INTERIM_STATUS_INFORCE,
                'canSetStatus' => in_array($interimStatus, $this->canSetStatusMap),
                'canUpdateInterim' => !in_array($interimStatus, $this->restrictedUpdateMap),
            ]
        );
    }
}
