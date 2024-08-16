<?php

/**
 * Close Tasks
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Task\Task;

/**
 * Close Tasks
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CloseTasks extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Task';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $id) {

            /** @var Task $task */
            $task = $this->getRepo()->fetchById($id);
            $task->setIsClosed('Y');
            $this->getRepo()->save($task);
        }

        $result->addMessage(count($command->getIds()) . ' Task(s) closed');

        return $result;
    }
}
