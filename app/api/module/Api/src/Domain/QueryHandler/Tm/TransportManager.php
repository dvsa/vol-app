<?php

/**
 * Transport Manager
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Tm;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Transport Manager
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManager extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManager';

    protected $extraRepos = ['Note'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $repo Dvsa\Olcs\Api\Domain\Repository\TransportManager */
        $repo = $this->getRepo();
        /* @var $transportManager \Dvsa\Olcs\Api\Entity\Tm\TransportManager */
        $transportManager = $repo->fetchUsingId($query);

        $this->auditRead($transportManager);

        $latestNote = $this->getRepo('Note')->fetchForOverview(null, null, $transportManager->getId());

        return $this->result(
            $transportManager,
            [
                'tmType',
                'tmStatus',
                'homeCd' => [
                    'person' => [
                        'title'
                    ],
                    'address' => [
                        'countryCode'
                    ]
                ],
                'workCd' => [
                    'address' => [
                        'countryCode'
                    ]
                ],
                'users',
                'mergeToTransportManager' => [
                    'homeCd' => ['person']
                ]
            ],
            [
                'hasUsers' => (count($transportManager->getUsers()) > 0 ? $transportManager->getUsers() : false),
                'hasBeenMerged' => !empty($transportManager->getMergeToTransportManager()),
                'latestNote' => $latestNote
            ]
        );
    }
}
