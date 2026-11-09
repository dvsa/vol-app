<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\CreateCustom as Cmd;

/**
 * Add an empty caseworker-written to-do to an Other issue, after the letter's other to-dos (VOL-7735)
 */
final class CreateCustom extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstanceTodo';

    protected $extraRepos = ['LetterInstanceIssue'];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        /** @var LetterInstanceIssue $issue */
        $issue = $this->getRepo('LetterInstanceIssue')->fetchById((int) $command->getLetterInstanceIssue());
        $letterInstance = $issue->getLetterInstance();

        if (!$letterInstance->isOpenForChanges()) {
            throw new ValidationException(['To-dos can only be added to a draft or ready letter']);
        }

        if (!$issue->isOther()) {
            throw new ValidationException(['To-dos can only be added to an Other issue']);
        }

        $lastOrder = 0;
        foreach ($letterInstance->getLetterInstanceTodos() as $existing) {
            $lastOrder = max($lastOrder, (int) $existing->getDisplayOrder());
        }

        $todo = new LetterInstanceTodo();
        $todo->setDisplayOrder($lastOrder + 1);
        $issue->addLetterInstanceTodo($todo);
        $letterInstance->addLetterInstanceTodo($todo);

        $this->getRepo()->save($todo);

        $this->result->addId('letterInstanceTodo', $todo->getId());
        $this->result->addMessage('To-do added');

        return $this->result;
    }
}
