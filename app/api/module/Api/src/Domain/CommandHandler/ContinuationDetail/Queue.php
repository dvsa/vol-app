<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Queue letters
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Queue extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'ContinuationDetail';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws RuntimeException
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();
        $result = new Result();
        foreach ($ids as $continuationDetailId) {
            $createCmd = CreateQueueCmd::create(
                [
                    'entityId' => $continuationDetailId,
                    'type' => $command->getType(),
                    'status' => QueueEntity::STATUS_QUEUED
                ]
            );
            $result->merge($this->handleSideEffect($createCmd));

        }

        $result->addMessage('All letters queued');

        return $result;
    }
}
