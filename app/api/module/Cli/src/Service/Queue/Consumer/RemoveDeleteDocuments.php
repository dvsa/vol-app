<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Domain\Command\Document\RemoveDeletedDocuments as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Remove Delete Documents queue processor
 */
class RemoveDeleteDocuments extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get data for the DTO command
     *
     * @param QueueEntity $item Queue entity being processed
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return [];
    }
}
