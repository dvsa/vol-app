<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Laminas\Json\Json as LaminasJson;

/**
 * Queue Aware
 */
trait QueueAwareTrait
{
    /**
     * Generates command for adding an email to the queue
     *
     * @param string      $cmdClass         email command class
     * @param array       $cmdData          command data
     * @param int         $entityId         id of the record being processed
     * @param string|null $processAfterDate queue job won't be processed before this date
     *
     * @return CreateQueue
     */
    public function emailQueue($cmdClass, array $cmdData, $entityId, $processAfterDate = null)
    {
        $options =                     [
            'commandClass' => $cmdClass,
            'commandData' => $cmdData,
        ];

        return $this->createQueue($entityId, Queue::TYPE_EMAIL, $options, $processAfterDate);
    }

    /**
     * Generates command for adding a nysiis request to the queue
     *
     * @param int         $entityId         entity id (transport manager)
     * @param string|null $processAfterDate wait until this date to process
     *
     * @return CreateQueue
     */
    public function nysiisQueueCmd($entityId, $processAfterDate = null)
    {
        return $this->createQueue($entityId, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => $entityId], $processAfterDate);
    }

    /**
     * Generates command for adding a queue job
     *
     * @param int         $entityId         id of the record being processed
     * @param string      $type             queue type
     * @param array       $options          queue options
     * @param string|null $processAfterDate queue job won't be processed before this date
     *
     * @return CreateQueue
     */
    public function createQueue($entityId, $type, array $options, $processAfterDate = null)
    {
        return CreateQueue::create(
            [
                'entityId' => $entityId,
                'type' => $type,
                'status' => Queue::STATUS_QUEUED,
                'options' => LaminasJson::encode($options),
                'processAfterDate' => $processAfterDate
            ]
        );
    }
}
