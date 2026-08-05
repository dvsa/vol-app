<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo\UpdateContent as Cmd;

/**
 * Update LetterInstanceTodo edited description
 *
 * Stores caseworker edits on the instance only. The canonical
 * letter_todo_version is never modified, so a to-do reworded for one
 * operator keeps its standing wording in every other letter.
 */
final class UpdateContent extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstanceTodo';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo $letterInstanceTodo */
        $letterInstanceTodo = $this->getRepo()->fetchUsingId($command);

        $letterInstanceTodo->setEditedDescriptionFromArray(
            json_decode($command->getEditedDescription(), true)
        );

        $this->getRepo()->save($letterInstanceTodo);

        $this->result->addId('letterInstanceTodo', $letterInstanceTodo->getId());
        $this->result->addMessage('To-do wording updated successfully');

        return $this->result;
    }
}
