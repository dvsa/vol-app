<?php

/**
 * Create Queue item
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateCmd;

/**
 * Create Queue item
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'Queue';

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var CreateCmd $command
         */
        $queue = new QueueEntity();

        $queue->validateQueue($command->getType(), $command->getStatus(), $command->getProcessAfterDate());

        $queue->setType(
            $this->getRepo()->getRefdataReference($command->getType())
        );

        $queue->setStatus(
            $this->getRepo()->getRefdataReference($command->getStatus())
        );

        $queue->setEntityId($command->getEntityId());

        if ($command->getOptions()) {
            $queue->setOptions($command->getOptions());
        }

        //using not empty as potentially we could end up with empty string instead of null
        //date has been validated using validateQueue above
        if (!empty($command->getProcessAfterDate())) {
            $processAfterDate = new \DateTime($command->getProcessAfterDate());
            $queue->setProcessAfterDate($processAfterDate);
        }

        $this->getRepo()->save($queue);

        $result = new Result();
        $result->addId('queue', $queue->getId(), true);
        $result->addMessage('Queue created');
        return $result;
    }
}
