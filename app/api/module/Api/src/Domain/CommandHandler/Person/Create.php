<?php

/**
 * Create a Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Person;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create a Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'Person';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $person Person */
        $person = $this->createPersonObject($command);
        $this->getRepo()->save($person);

        $result = new Result();
        $result->addId("person", $person->getId());
        $result->addMessage("Person ID {$person->getId()} created");

        return $result;
    }

    protected function createPersonObject($command)
    {
        $person = new PersonEntity();
        $person->updatePerson(
            $command->getFirstName(),
            $command->getLastName(),
            $this->getRepo()->getRefdataReference($command->getTitle()),
            $command->getBirthDate(),
            $command->getBirthPlace()
        );
        return $person;
    }
}
