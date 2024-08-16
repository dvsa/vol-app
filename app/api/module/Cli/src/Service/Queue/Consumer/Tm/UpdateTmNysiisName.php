<?php

/**
 * Update TM name with Nysiis data
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Tm\UpdateNysiisName as Cmd;
use Laminas\Serializer\Adapter\Json as LaminasJson;

/**
 * Update TM name with Nysiis data
 */
class UpdateTmNysiisName extends AbstractCommandConsumer
{
    /**
     * @var string the command class
     */
    protected $commandName = Cmd::class;

    /**
     * @var int Max retry attempts before fails
     */
    protected $maxAttempts = 4;

    /**
     * gets command data
     *
     * @param QueueEntity $item queue item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $json = new LaminasJson();
        return array_merge(
            [
                'id' => $item->getEntityId()
            ],
            $json->unserialize($item->getOptions())
        );
    }
}
