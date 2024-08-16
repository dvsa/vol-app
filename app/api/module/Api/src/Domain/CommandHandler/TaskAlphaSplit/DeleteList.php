<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * DeleteList of TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TaskAlphaSplit';

    public function handleCommand(CommandInterface $command)
    {
        $taskAlphaSplits = $this->getRepo()->fetchByIds($command->getIds());

        /* @var $taskAlphaSplit \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit */
        foreach ($taskAlphaSplits as $taskAlphaSplit) {
            $this->getRepo()->delete($taskAlphaSplit);
            $this->result->addMessage("Task Alpha Split ID {$taskAlphaSplit->getId()} deleted");
        }

        return $this->result;
    }
}
