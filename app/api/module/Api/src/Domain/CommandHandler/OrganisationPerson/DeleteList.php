<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a list of OrganisationPerson
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'OrganisationPerson';

    protected $extraRepos = ['Person', 'ApplicationOrganisationPerson'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $org = null;
        foreach ($command->getIds() as $organisationPersonId) {
            /** @var \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson $organisationPerson */
            $organisationPerson = $this->getRepo()->fetchById($organisationPersonId);
            $this->getRepo()->delete($organisationPerson);
            $this->result->addMessage("OrganisationPerson ID {$organisationPersonId} deleted");

            $org = $organisationPerson->getOrganisation();
            $person = $organisationPerson->getPerson();

            /** @var Repository\OrganisationPerson $orgPersonRepo */
            $orgPersonRepo = $this->getRepo('OrganisationPerson');
            $organisationPersons = $orgPersonRepo->fetchListForPerson($person->getId());

            if (count($organisationPersons) === 0) {
                // if no organisation person records for this person, then person can be deleted
                $this->getRepo('Person')->delete($person);
                $this->result->addMessage("Person ID {$person->getId()} deleted");

                // remove all application_organisation_person records for this person
                /** @var Repository\ApplicationOrganisationPerson $appOrgPersonRepo */
                $appOrgPersonRepo = $this->getRepo('ApplicationOrganisationPerson');
                $appOrgPersonRepo->deleteForPerson($person);
            }
        }

        //  update Organisation Name
        if ($org !== null && ($org->isSoleTrader() || $org->isPartnership())) {
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

        return $this->result;
    }
}
