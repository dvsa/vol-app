<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Task;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTask extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_TEAM_INVALID = 'task.edit.team.invalid';

    protected $repoServiceName = 'Task';

    /**
     * Command Handler
     *
     * @param TransferCmd\Task\UpdateTask $command Command
     *
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Repository\Task $repo */
        $repo = $this->getRepo();

        //  get User entity
        $userId = (int)$command->getAssignedToUser();
        $user = $repo->getReference(Entity\User\User::class, $userId);

        $team = $repo->getTeamReference((int)$command->getAssignedToTeam(), $userId);

        if ($team === null) {
            throw new ValidationException([self::ERR_TEAM_INVALID]);
        }

        //  Update Task
        /** @var Entity\Task\Task $task */
        $task = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $task
            ->setDescription($command->getDescription())
            ->setActionDate(new DateTime($command->getActionDate()))
            ->setUrgent($command->getUrgent())
            ->setCategory($repo->getCategoryReference($command->getCategory()))
            ->setSubCategory($repo->getSubCategoryReference($command->getSubCategory()))
            ->setAssignedToUser($user)
            ->setAssignedToTeam($team);

        $repo->save($task);

        $result = new Result();
        $result->addMessage('Task updated');

        return $result;
    }
}
