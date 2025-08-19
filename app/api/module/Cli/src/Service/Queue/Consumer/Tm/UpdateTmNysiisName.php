<?php

/**
 * Update TM name with Nysiis data
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Tm;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Tm\UpdateNysiisName as Cmd;

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
     */
    public function getCommandData(QueueEntity $item): array
    {
        return array_merge(
            json_decode($item->getOptions(), true),
            [
                'id' => $item->getEntityId()
            ]
        );
    }
}
