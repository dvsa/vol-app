<?php

namespace Dvsa\Olcs\Api\Entity\EventHistory;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * EventHistory Entity
 */
#[ORM\Table(name: 'event_history')]
#[ORM\Index(name: 'ix_event_history_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'ix_event_history_licence_id', columns: ['licence_id'])]
#[ORM\Index(name: 'ix_event_history_application_id', columns: ['application_id'])]
#[ORM\Index(name: 'ix_event_history_transport_manager_id', columns: ['transport_manager_id'])]
#[ORM\Entity]
class EventHistory extends AbstractEventHistory
{
    /**
     * Construct
     *
     * @param User             $user             User who performed the action
     * @param EventHistoryType $eventHistoryType Event history type
     * @param string           $eventData        Event data
     *
     * @return EventHistory
     */
    public function __construct(User $user, EventHistoryType $eventHistoryType, $eventData = null)
    {
        $this->user = $user;
        $this->eventHistoryType = $eventHistoryType;
        $this->eventData = $eventData;
        $this->eventDatetime = new \DateTime();
    }
}
