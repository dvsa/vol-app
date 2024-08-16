<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepository;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Delete as DeleteCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity;

/**
 * Delete a Note
 */
final class Delete extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Note';

    /**
     * @param DeleteCommand $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var NoteRepository $repo */
        $repo = $this->getRepo();

        $note = $repo->fetchUsingId($command);
        $repo->delete($note);

        $result->addMessage('Note deleted');

        return $result;
    }
}
