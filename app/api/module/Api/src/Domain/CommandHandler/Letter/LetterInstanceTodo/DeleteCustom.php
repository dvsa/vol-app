<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterOpenForChangesTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a caseworker-written to-do. Library to-dos come and go with their issues (VOL-7735)
 */
final class DeleteCustom extends AbstractCommandHandler
{
    use LetterOpenForChangesTrait;

    protected $repoServiceName = 'LetterInstanceTodo';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var LetterInstanceTodo $todo */
        $todo = $this->getRepo()->fetchUsingId($command);

        if (!$todo->getLetterInstance()->isOpenForChanges()) {
            throw new ValidationException(['To-dos can only be deleted from a draft or ready letter']);
        }

        if (!$todo->isCustom()) {
            throw new ValidationException(['Only to-dos written on this letter can be deleted']);
        }

        $this->recordLetterChange($todo->getLetterInstance());

        $todo->getLetterInstanceIssue()?->removeLetterInstanceTodo($todo);
        $todo->getLetterInstance()->removeLetterInstanceTodo($todo);

        $this->saveLetterChange(fn () => $this->getRepo()->delete($todo));

        $this->result->addMessage('To-do deleted');

        return $this->result;
    }
}
