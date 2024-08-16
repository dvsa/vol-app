<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\GenerateCheckListReminder as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Continuation (Digital) send paper version of incomplete continuation
 */
class ContinuationDigitalReminder extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get data for the DTO command
     *
     * @param QueueEntity $item Queue
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return ['id' => $item->getEntityId(), 'user' => $item->getCreatedBy()->getId()];
    }
}
