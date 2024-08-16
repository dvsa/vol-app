<?php

/**
 * Allocate IRHP Application Permits Consumer
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\AllocateIrhpApplicationPermits as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;

/**
 * Allocate IRHP Application Permits Consumer
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AllocateIrhpApplicationPermits extends AbstractCommandConsumer
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
        return ['id' => $item->getEntityId()];
    }
}
