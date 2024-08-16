<?php

/**
 * Create Psv Vehicle List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\DiscPrinting;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Discs\BatchVehicleListGeneratorForPsvDiscs as Cmd;

/**
 * Create Psv Vehicle List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreatePsvVehicleList extends AbstractCommandConsumer
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
            'bookmarks' => $options['bookmarks'],
            'queries'   => $options['queries'],
            'user' => $options['user'] ?? $item->getCreatedBy()->getId()
        ];
    }
}
