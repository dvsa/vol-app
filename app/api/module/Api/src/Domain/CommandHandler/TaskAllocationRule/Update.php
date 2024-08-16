<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TaskAllocationRule\Update as Cmd;

final class Update extends AbstractCommandHandler
{
    use UpdateTaskAllocationRuleTrait;

    protected $extraRepos = [
        Repository\TaskAllocationRule::class
    ];

    /**
     * @param Cmd|CommandInterface $command Cmd
     * @return Result
     * @throws ORMException
     * @throws RuntimeException
     * @throws NotFoundException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $repo = $this->getRepo(Repository\TaskAllocationRule::class);

        $taskAllocationRule = $repo->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());

        $taskAllocationRule = $this->updateTaskAllocationRule($taskAllocationRule, $repo, $command);

        $repo->save($taskAllocationRule);

        $this->result->addId('task-allocation-rule', $taskAllocationRule->getId());
        $this->result->addMessage('TaskAllocationRule updated');

        return $this->result;
    }
}
