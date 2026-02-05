<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as LetterTodoEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Create as Cmd;

/**
 * Create LetterTodo
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterTodo';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $entity = new LetterTodoEntity();

        // Set working properties - versioning will be handled by repository
        $entity->setDescription($command->getDescription());
        $entity->setHelpText($command->getHelpText());

        $this->getRepo()->save($entity);

        $this->result->addId('letterTodo', $entity->getId());
        $this->result->addMessage('Letter Todo created');

        return $this->result;
    }
}
