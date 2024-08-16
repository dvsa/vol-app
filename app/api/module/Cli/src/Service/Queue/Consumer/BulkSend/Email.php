<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\BulkSend;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\BulkSend\Email as Cmd;

/**
 * Bulk Send Emails
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class Email extends AbstractCommandConsumer
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
            'documentIdentifier' => $options['identifier'],
            'user' => $options['user'],
            'templateName' => $options['templateName']
        ];
    }
}
