<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command as TransferCmd;

/**
 * Update OrganisationPerson
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OrganisationPerson';
    protected $extraRepos = ['Person'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\OrganisationPerson\Update $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //  save position
        /* @var \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson $organisationPerson */
        $organisationPerson = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $organisationPerson->setPosition($command->getPosition());
        $this->getRepo()->save($organisationPerson);

        //  save person data
        $personData = $command->getPerson();

        $person = $organisationPerson->getPerson();
        $person->updatePerson(
            $personData['forename'],
            $personData['familyName'],
            $this->getRepo()->getRefdataReference($personData['title']),
            $personData['birthDate']
        );
        $person->setOtherName($personData['otherName']);

        $this->getRepo('Person')->save($person);

        //  generate organisation name
        /** @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation $org */
        $org = $organisationPerson->getOrganisation();

        if ($org->isSoleTrader() || $org->isPartnership()) {
            $this->result->merge(
                $this->handleSideEffect(
                    TransferCmd\Organisation\GenerateName::create(
                        [
                            'organisation' => $org->getId(),
                        ]
                    )
                )
            );
        }

        return $this->result
            ->addMessage('OrganisationPerson updated')
            ->addId('organisationPerson', $organisationPerson->getId());
    }
}
