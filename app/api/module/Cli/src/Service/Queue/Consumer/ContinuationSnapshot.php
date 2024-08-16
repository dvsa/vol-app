<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\CreateSnapshot as Cmd;

/**
 * Create continuation snapshot
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContinuationSnapshot extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get command data
     *
     * @param QueueEntity $item item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return ['id' => $item->getEntityId(), 'user' => $item->getCreatedBy()->getId()];
    }
}
