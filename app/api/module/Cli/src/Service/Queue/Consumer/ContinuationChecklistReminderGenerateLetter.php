<?php

/**
 * Continuation Checklist Reminder Generate Letter Queue Consumer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\ProcessReminder as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Continuation Checklist Reminder Generate Letter Queue Consumer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContinuationChecklistReminderGenerateLetter extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return ['id' => $item->getEntityId(), 'user' => $item->getCreatedBy()->getId()];
    }
}
