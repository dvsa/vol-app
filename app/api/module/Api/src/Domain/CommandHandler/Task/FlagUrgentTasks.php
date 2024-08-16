<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Task\Task;

/**
 * Close FlagUrgentTasks
 */
final class FlagUrgentTasks extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Task';

    /**
     * Handle Command
     *
     * @param CommandInterface|\Dvsa\Olcs\Transfer\Command\Task\FlagUrgentTasks $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $updatedTaskCount = $this->getRepo()->flagUrgentsTasks();

        $this->result->addMessage("{$updatedTaskCount} task(s) flagged as urgent");

        return $this->result;
    }
}
