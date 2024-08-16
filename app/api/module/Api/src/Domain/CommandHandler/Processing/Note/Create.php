<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepository;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Exception;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity;

/**
 * Create a Note
 */
final class Create extends CreateUpdateAbstract implements TransactionedInterface
{
    /**
     * @param CreateCommand $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var NoteRepository $repo */
        $repo = $this->getRepo();

        $note = $this->getNoteEntity($command);
        $note->setComment($command->getComment());
        $note->setPriority($command->getPriority());

        $this->getRepo()->save($note);

        $result->addId('note', $note->getId());
        $result->addMessage('Note created');

        return $result;
    }

    /**
     * @param CommandInterface $command
     * @return Entity\Note\Note
     */
    protected function retrieveEntity(CommandInterface $command)
    {
        return new Entity\Note\Note();
    }
}
