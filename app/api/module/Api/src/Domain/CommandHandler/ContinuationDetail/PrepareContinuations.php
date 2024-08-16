<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetaillEntityService;

/**
 * Prepare letters
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class PrepareContinuations extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();
        $result = new Result();
        foreach ($ids as $continuationDetailId) {
            $continuationDetail = $this->getRepo()->fetchById($continuationDetailId);
            $continuationDetail->setStatus(
                $this->getRepo()->getRefdataReference(ContinuationDetaillEntityService::STATUS_PRINTING)
            );
            $this->getRepo()->save($continuationDetail);
            $createCmd = CreateQueueCmd::create(
                [
                    'entityId' => $continuationDetailId,
                    'type' => QueueEntity::TYPE_CONT_CHECKLIST,
                    'status' => QueueEntity::STATUS_QUEUED
                ]
            );
            $result->merge($this->handleSideEffect($createCmd));
        }

        $result->addMessage('All letters queued');

        return $result;
    }
}
