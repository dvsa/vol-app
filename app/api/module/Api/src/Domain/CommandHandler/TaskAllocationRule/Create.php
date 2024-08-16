<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule;

use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TaskAllocationRule\Create as Cmd;

final class Create extends AbstractCommandHandler
{
    use UpdateTaskAllocationRuleTrait;

    protected $extraRepos = [
        Repository\TaskAllocationRule::class
    ];

    /**
     * @param $command Cmd
     * @throws ORMException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $repo = $this->getRepo(Repository\TaskAllocationRule::class);

        $taskAllocationRule = new Entity\Task\TaskAllocationRule();

        $taskAllocationRule = $this->updateTaskAllocationRule($taskAllocationRule, $repo, $command);

        $repo->save($taskAllocationRule);

        $this->result->addId('task-allocation-rule', $taskAllocationRule->getId());
        $this->result->addMessage('TaskAllocationRule created');

        return $this->result;
    }
}
