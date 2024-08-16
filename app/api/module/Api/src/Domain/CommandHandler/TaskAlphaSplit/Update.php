<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\Update as Cmd;

/**
 * Update TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'TaskAlphaSplit';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Cmd */
        $repo = $this->getRepo();

        /* @var $taskAlphaSplit \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit */
        $taskAlphaSplit = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $taskAlphaSplit->setUser(
            $repo->getReference(\Dvsa\Olcs\Api\Entity\User\User::class, $command->getUser())
        );
        $taskAlphaSplit->setLetters($command->getLetters());

        $repo->save($taskAlphaSplit);

        $this->result->addId('task-alpha-split', $taskAlphaSplit->getId());
        $this->result->addMessage('TaskAlphaSplit updated');

        return $this->result;
    }
}
