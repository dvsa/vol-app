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

            if ($command->getType() === QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER) {
                $result->merge($this->generateTask($continuationDetailId));
            }
        }

        $result->addMessage('All letters queued');

        return $result;
    }

    /**
     * @param $continuationDetailId
     * @return Result
     * @throws RuntimeException
     */
    private function generateTask($continuationDetailId): Result
    {
        /** @var ContinuationDetail $continuationDetail */
        $continuationDetail = $this->getRepo()->fetchById($continuationDetailId);
        $user = $this->getCurrentUser();
        $taskCmd = CreateTask::create([
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
            'description' => 'Check if checklist has been received',
            'actionDate' => (new DateTime('+14 days'))->format('Y-m-d'),
            'licence' => $continuationDetail->getLicence()->getId(),
            'assignedToUser' => $user->getId(),
        ]);
        return $this->handleSideEffect($taskCmd);
    }
}
