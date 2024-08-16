<?php

/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Discs\PrintDiscs as Cmd;

/**
 * Print Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscs extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = json_decode($item->getOptions(), true);
        return [
            'discs' => $options['discs'],
            'type' => $options['type'],
            'startNumber' => $options['startNumber'],
            'user' => $options['user'] ?? $item->getCreatedBy()->getId()
        ];
    }
}
