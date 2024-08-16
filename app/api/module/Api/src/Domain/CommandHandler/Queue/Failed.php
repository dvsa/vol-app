<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Queue\Delete as DeleteQueueCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Failed extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Queue';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Queue\Failed $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Queue $repo */
        $repo = $this->getRepo();

        $entity = $command->getItem()
            ->setStatus(
                $repo->getRefdataReference(QueueEntity::STATUS_FAILED)
            );

        $error = $command->getLastError();

        if ($error === QueueEntity::ERR_MAX_ATTEMPTS) {
            // keep the existing error as well
            $error .= ': ' . $entity->getLastError();
        }
        $entity->setLastError(trim($error));

        $repo->save($entity);

        $queueId = $entity->getId();

        $result = new Result();
        $result
            ->addId('queue', $queueId)
            ->addMessage('Queue item marked failed');

        $result->merge($this->handleSideEffect(DeleteQueueCmd::create(['id' => $queueId])));

        return $result;
    }
}
