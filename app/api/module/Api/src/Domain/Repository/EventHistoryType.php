<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;

/**
 * Event History Type
 */
class EventHistoryType extends AbstractRepository
{
    protected $entity = EventHistoryTypeEntity::class;
}
