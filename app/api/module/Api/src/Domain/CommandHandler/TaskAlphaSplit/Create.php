<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\Create as Cmd;

/**
 * Create TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'TaskAlphaSplit';

    protected $extraRepos = ['TaskAllocationRule'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Cmd */
        $repo = $this->getRepo();

        $taskAlphaSplit = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();
        $taskAlphaSplit->setTaskAllocationRule(
            $repo->getReference(\Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule::class, $command->getTaskAllocationRule())
        );
        $taskAlphaSplit->setUser(
            $repo->getReference(\Dvsa\Olcs\Api\Entity\User\User::class, $command->getUser())
        );
        $taskAlphaSplit->setLetters($command->getLetters());

        $repo->save($taskAlphaSplit);

        // when creating a new alpha split make sure the allocation rule does not have a user assigned
        $taskAlphaSplit->getTaskAllocationRule()->setUser(null);
        $this->getRepo('TaskAllocationRule')->save($taskAlphaSplit->getTaskAllocationRule());

        $this->result->addId('task-alpha-split', $taskAlphaSplit->getId());
        $this->result->addMessage('TaskAlphaSplit created');

        return $this->result;
    }
}
