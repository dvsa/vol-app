<?php

/**
 * Generate permits
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\GeneratePermits as Cmd;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Generate permits
 */
class GeneratePermits extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * @param QueueEntity $item Queue item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = json_decode($item->getOptions(), true);
        return [
            'ids' => $options['ids'],
            'user' => $options['user'],
        ];
    }
}
