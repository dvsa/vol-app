<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepository;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Update as UpdateCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity;

/**
 * Update a Note
 */
final class Update extends CreateUpdateAbstract implements TransactionedInterface
{
    /**
     * @param UpdateCommand $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var NoteRepository $repo */
        $repo = $this->getRepo();

        $note = $this->getNoteEntity($command);
        $note->setPriority($command->getPriority());

        $this->getRepo()->save($note);

        $result->addId('note', $note->getId());
        $result->addMessage('Note updated');

        return $result;
    }

    /**
     * @param CommandInterface $command
     * @return Entity\Note\Note
     */
    protected function retrieveEntity(CommandInterface $command)
    {
        return $this->getRepo()->fetchById($command->getId(), Query::HYDRATE_OBJECT, $command->getVersion());
    }
}
