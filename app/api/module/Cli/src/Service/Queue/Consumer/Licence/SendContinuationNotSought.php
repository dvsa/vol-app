<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Licence;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Email\SendContinuationNotSought as Cmd;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Send CNS
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SendContinuationNotSought extends AbstractCommandConsumer
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
            'licences' => $options['licences'],
            'date' => new DateTime($options['date']['date'])
        ];
    }
}
