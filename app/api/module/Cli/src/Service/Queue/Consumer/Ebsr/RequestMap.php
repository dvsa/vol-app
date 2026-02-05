<?php

/**
 * Request Map
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;

/**
 * Request Map
 */
class RequestMap extends AbstractCommandConsumer
{
    public const TASK_FAIL_DESC = 'Route map generation for: %s failed';

    /**
     * @var string the command class
     */
    protected $commandName = Cmd::class;

    /**
     * gets command data
     *
     * @param QueueEntity $item queue item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        return array_merge(
            json_decode($item->getOptions(), true),
            ['user' => $item->getCreatedBy()->getId()]
        );
    }

    /**
     * Creates a task if the map request has failed, then calls the usual failure method
     *
     * @param QueueEntity $item   queue item
     * @param string|null $reason reason for failure
     *
     * @return string
     */
    #[\Override]
    public function failed(QueueEntity $item, $reason = null)
    {
        $cmdData = $this->getCommandData($item);

        $data = [
            'category' => TaskEntity::CATEGORY_BUS,
            'subCategory' => TaskEntity::SUBCATEGORY_EBSR,
            'description' => sprintf(self::TASK_FAIL_DESC, $cmdData['regNo']),
            'actionDate' => date('Y-m-d'),
            'busReg' => $cmdData['id'],
            'licence' => $cmdData['licence'],
        ];

        $command = CreateTaskCmd::create($data);

        $this->handleCommand($command);

        return parent::failed($item, $reason);
    }
}
