<?php

/**
 * Update a Person, all fields
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Person;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update a Person, all fields
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateFull extends AbstractCommandHandler
{
    protected $repoServiceName = 'Person';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $person Person */
        $person = $this->getRepo()->fetchById($command->getId());

        $person->updatePerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getTitle() ? $this->getRepo()->getRefdataReference($command->getTitle()) : null,
            $command->getBirthDate(),
            $command->getBirthPlace()
        );
        $this->getRepo()->save($person);

        $result = new Result();
        $result->addId("person", $person->getId());
        $result->addMessage("Person updated");

        return $result;
    }
}
