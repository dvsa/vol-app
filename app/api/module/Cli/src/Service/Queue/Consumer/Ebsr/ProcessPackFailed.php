<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackFailed as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Set EBSR Submission as failed
 */
class ProcessPackFailed extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    public function getCommandData(QueueEntity $item): array
    {
        return json_decode($item->getOptions(), true);
    }
}
