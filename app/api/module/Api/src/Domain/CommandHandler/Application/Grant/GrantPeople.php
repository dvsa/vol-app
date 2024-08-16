<?php

/**
 * Grant People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostDeletePeopleGrantTask
    as CreatePostDeletePeopleGrantTaskCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostAddPeopleGrantTask as CreatePostAddPeopleGrantTaskCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantPeople as GrantPeopleCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrganisationPersonRepository;
use Dvsa\Olcs\Api\Domain\Util\EntityCloner;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;

/**
 * Grant People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantPeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Person', 'OrganisationPerson'];

    /**
     * @param GrantPeopleCommand|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $applicationOrgPeople = $application->getApplicationOrganisationPersons();

        if ($applicationOrgPeople->count() < 1) {
            return $this->result;
        }

        /** @var ApplicationOrganisationPerson $applicationOrgPerson */
        foreach ($applicationOrgPeople as $applicationOrgPerson) {
            switch ($applicationOrgPerson->getAction()) {
                case 'A':
                    $newPersonID = $this->createOrganisationPerson($applicationOrgPerson);
                    $this->result->addId('createdPerson', $newPersonID, true);
                    break;
                case 'U':
                    $this->updateOrganisationPerson($applicationOrgPerson);
                    break;
                case 'D':
                    $this->deleteOrganisationPerson($applicationOrgPerson);
                    break;
            }
        }

        $this->result->addMessage('Organisation person records have been copied');

        $this->result->merge(
            $this->handleSideEffects(
                [
                    CreatePostDeletePeopleGrantTaskCommand::create(['applicationId' => $command->getId()]),
                    CreatePostAddPeopleGrantTaskCommand::create(['applicationId' => $command->getId()]),
                ]
            )
        );

        return $this->result;
    }

    /**
     * Create a person and associate it with a new organisation person record
     *
     * @param ApplicationOrganisationPerson $aop application organisation person
     *
     * @return int ID of the new person
     */
    private function createOrganisationPerson(ApplicationOrganisationPerson $aop)
    {
        $sourcePerson = $aop->getPerson();
        $targetPerson = EntityCloner::cloneEntity($sourcePerson);

        $this->getRepo('Person')->save($targetPerson);

        $ignore = ['action', 'originalPerson', 'person'];

        /** @var OrganisationPerson $targetOp */
        $targetOp = EntityCloner::cloneEntityInto($aop, OrganisationPerson::class, $ignore);

        /** Application */
        $targetOp->setPerson($targetPerson);

        $this->getOrganisationRepository()->save($targetOp);

        return $targetOp->getPerson()->getId();
    }

    /**
     * Updates are actually just a combination of a delete
     * of the original person and an add of the new one
     *
     * @param ApplicationOrganisationPerson $aop application organisation person
     *
     * @return void
     */
    private function updateOrganisationPerson(ApplicationOrganisationPerson $aop)
    {
        $this->deleteByOrgAndPerson($aop->getOrganisation(), $aop->getOriginalPerson());

        $this->createOrganisationPerson($aop);
    }

    /**
     * Delete a person
     *
     * @param ApplicationOrganisationPerson $aop application organisation person
     *
     * @return void
     */
    private function deleteOrganisationPerson(ApplicationOrganisationPerson $aop)
    {
        $this->deleteByOrgAndPerson($aop->getOrganisation(), $aop->getPerson());
    }

    /**
     * Helper to delete both an org row and the person it
     * links to
     *
     * @param Organisation $org    organisation
     * @param Person       $person person
     *
     * @return void
     */
    private function deleteByOrgAndPerson(Organisation $org, Person $person)
    {
        $orgPersonRecords = $this->getOrganisationRepository()->fetchByOrgAndPerson($org, $person);

        foreach ($orgPersonRecords as $orgPersonRecord) {
            $this->getOrganisationRepository()->delete($orgPersonRecord);
        }
    }

    /**
     * Get the OrganisationPerson repository
     *
     * @return OrganisationPersonRepository
     */
    private function getOrganisationRepository()
    {
        /** @var OrganisationPersonRepository $repository */
        $repository = $this->getRepo('OrganisationPerson');
        return $repository;
    }
}
