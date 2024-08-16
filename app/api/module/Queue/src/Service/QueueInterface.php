<?php

namespace Dvsa\Olcs\Queue\Service;

use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;

interface QueueInterface
{
    public function setQueueService(Queue $queueService): void;
    public function setMessageBuilderService(MessageBuilder $messageBuilderService): void;
    public function setQueueConfig(array $queueConfig): void;
}
