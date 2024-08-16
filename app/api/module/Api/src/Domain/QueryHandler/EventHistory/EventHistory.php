<?php

/**
 * EventHistory
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\EventHistory;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * EventHistory
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EventHistory extends AbstractQueryHandler
{
    protected $repoServiceName = 'EventHistory';

    public function handleQuery(QueryInterface $query)
    {
        $this->getRepo()->disableSoftDeleteable();
        $eventHistory = $this->getRepo()->fetchUsingId($query);
        return $this->result(
            $eventHistory,
            [
                'user' => [
                    'contactDetails' => ['person']
                ],
                'eventHistoryType'
            ],
            [
                'eventHistoryDetails' => $this->getEventHistoryDetails(
                    $eventHistory->getEntityPk(),
                    $eventHistory->getEntityVersion(),
                    $eventHistory->getEntityType()
                )
            ]
        );
    }

    /**
     * Get event history details
     *
     * @param int $entityPk
     * @param int $entityVersion
     * @param string $entityType
     * @return array
     */
    public function getEventHistoryDetails($entityPk = null, $entityVersion = null, $entityType = null)
    {
        if (!$entityType || !$entityPk || !$entityVersion) {
            return [];
        }
        try {
            $eventHistoryDetails = $this->getRepo()
                ->fetchEventHistoryDetails($entityPk, $entityVersion, $entityType . '_hist');
        } catch (\Exception) {
            $eventHistoryDetails = [];
        }
        return $eventHistoryDetails;
    }
}
