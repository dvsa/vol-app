<?php

/**
 * Run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\RunScoring as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;

/**
 * Run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RunScoring extends AbstractCommandConsumer
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
        $options = json_decode($item->getOptions(), true);

        return [
            'id' => $item->getEntityId(),
            'deviation' => $options['deviation']
        ];
    }
}
