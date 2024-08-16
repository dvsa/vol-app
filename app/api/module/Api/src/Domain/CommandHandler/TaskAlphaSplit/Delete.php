<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete of TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Delete extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TaskAlphaSplit';

    public function handleCommand(CommandInterface $command)
    {
        $taskAlphaSplit = $this->getRepo()->fetchUsingId($command);
        $this->getRepo()->delete($taskAlphaSplit);

        $this->result->addMessage("Task Alpha Split ID {$taskAlphaSplit->getId()} deleted");

        return $this->result;
    }
}
