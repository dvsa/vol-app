<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;

class CreateTask extends AbstractCommandConsumer
{
    protected $commandName = CreateTaskCmd::class;

    /**
     * @inheritDoc
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = json_decode($item->getOptions(), true);
        return [
            'category' => $options['category'],
            'subCategory' => $options['subCategory'],
            'description' => $options['description'],
            'actionDate' => $options['actionDate'],
            'licence' => $options['licence'],
            'urgent' => $options['urgent'],
            'assignedToTeam' => $options['assignedToTeam']
        ];
    }
}
