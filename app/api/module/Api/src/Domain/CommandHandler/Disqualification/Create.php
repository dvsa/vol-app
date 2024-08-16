<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Disqualification\Create as Command;

/**
 * Creates a Disqualification
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Disqualification';
    protected $extraRepos = ['Person', 'ContactDetails'];

    /**
     * Creates a Disqualification
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        $disqualification = new Disqualification($this->getOrganisation($command), $this->getPerson($command));
        $disqualification->update(
            $command->getIsDisqualified(),
            $command->getStartDate() ? new \DateTime($command->getStartDate()) : null,
            $command->getNotes(),
            $command->getPeriod() ?: null
        );

        $this->getRepo()->save($disqualification);

        $result = new Result();

        $result->addId('disqualification', $disqualification->getId());
        $result->addMessage('Disqualification created');

        return $result;
    }

    /**
     * Get a refernece to the Organisation entity
     *
     *
     * @return Organisation|null
     */
    private function getOrganisation(Command $command)
    {
        return $command->getOrganisation() ?
            $this->getRepo()->getReference(Organisation::class, $command->getOrganisation()) :
            null;
    }

    /**
     * Get a refernece to the Person entity
     *
     *
     * @return \Dvsa\Olcs\Api\Entity\Person\Person|null
     */
    private function getPerson(Command $command)
    {
        return $command->getPerson() ?
            $this->getRepo()->getReference(\Dvsa\Olcs\Api\Entity\Person\Person::class, $command->getPerson()) :
            null;
    }
}
