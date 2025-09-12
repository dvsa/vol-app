<?php

/**
 * Command Consumer
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Command Consumer
 */
class CommandConsumer extends AbstractCommandConsumer
{
    /**
     * @param QueueEntity $item
     * @return string
     */
    protected function getCommandName(QueueEntity $item)
    {
        return json_decode($item->getOptions(), true)['commandClass'];
    }

    /**
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return json_decode($item->getOptions(), true)['commandData'];
    }
}
