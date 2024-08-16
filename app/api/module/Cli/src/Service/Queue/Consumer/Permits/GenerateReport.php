<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GenerateReport as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;

class GenerateReport extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get data for the DTO command
     *
     * @param QueueEntity $item Queue
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item): array
    {
        $cmdData = json_decode($item->getOptions(), true);
        $cmdData['user'] = $item->getCreatedBy()->getId();

        return $cmdData;
    }
}
