<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Reassign Tasks
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReassignTasks extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_TEAM_INVALID = 'task.edit.team.invalid';

    protected $repoServiceName = 'Task';

    /**
     * Command Handler
     *
     * @param TransferCmd\Task\ReassignTasks $command Command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Repository\Task $repo */
        $repo = $this->getRepo();

        $userId = (int)$command->getUser();
        $user = $repo->getReference(Entity\User\User::class, $userId);

        $team = $repo->getTeamReference((int)$command->getTeam(), $userId);

        if ($team === null) {
            throw new ValidationException([self::ERR_TEAM_INVALID]);
        }

        foreach ($command->getIds() as $id) {
            /** @var Entity\Task\Task $task */
            $task = $repo->fetchById($id);
            $task->setAssignedToUser($user);
            $task->setAssignedToTeam($team);

            $repo->save($task);
        }

        return (new Result())
            ->addMessage(count($command->getIds()) . ' Task(s) reassigned');
    }
}
