<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create an OrganisationPerson
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OrganisationPerson';
    protected $extraRepos = ['Person'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\OrganisationPerson\Create $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //  create person
        $personData = $command->getPerson();

        $person = new Entity\Person\Person();
        $person->updatePerson(
            $personData['forename'],
            $personData['familyName'],
            $this->getRepo()->getRefdataReference($personData['title']),
            $personData['birthDate']
        );
        $person->setOtherName($personData['otherName']);

        $this->getRepo('Person')->save($person);

        //  save organisation-person relation
        /** @var Entity\Organisation\Organisation $org */
        $org = $this->getRepo()->getReference(Entity\Organisation\Organisation::class, $command->getOrganisation());

        $orgPerson = new Entity\Organisation\OrganisationPerson();
        $orgPerson
            ->setPosition($command->getPosition())
            ->setOrganisation($org)
            ->setPerson($person);

        $this->getRepo()->save($orgPerson);

        //  update Organisation Name
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
            ->addId('organisationPerson', $orgPerson->getId())
            ->addId('person', $person->getId())
            ->addMessage("Organisation Person ID {$orgPerson->getId()} created");
    }
}
