<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Licence\ProcessContinuationNotSought as Cmd;

/**
 * Process CNS
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ProcessContinuationNotSought extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get command data
     *
     * @param QueueEntity $item item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = json_decode($item->getOptions(), true);
        return [
            'id' => $options['id'],
            'version' => $options['version']
        ];
    }
}
