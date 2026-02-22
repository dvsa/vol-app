<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Update as Cmd;

/**
 * Update LetterTodo
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterTodo';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterTodo $entity */
        $entity = $this->getRepo()->fetchUsingId($command);

        // Update working properties - versioning will be handled by repository
        $entity->setDescription($command->getDescription());

        if ($command->getHelpText() !== null) {
            $entity->setHelpText($command->getHelpText());
        }

        $this->getRepo()->save($entity);

        $this->result->addId('letterTodo', $entity->getId());
        $this->result->addMessage('Letter Todo updated');

        return $this->result;
    }
}
