<?php

/**
 * DeletePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;

/**
 * DeletePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeletePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['OrganisationPerson', 'ApplicationOrganisationPerson','Person'];

    /**
     * Handle comment
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\DeletePeople $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Licence\DeletePeople */

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);
        $result = new Result();

        foreach ($command->getPersonIds() as $personId) {
            if ($application->useDeltasInPeopleSection()) {
                $this->deltaDeletePerson($application, $personId, $result);
            } else {
                $this->deleteOrganisationPerson($application, $personId, $result);
            }
        }

        $dtoData = ['id' => $application->getId(), 'section' => 'people'];
        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create($dtoData)
            )
        );

        return $result;
    }

    /**
     * Delete a (Application)OrganisationPerson by using deltas
     *
     * @param ApplicationEntity $application Application
     * @param int               $personId    ID of person to delete
     * @param Result            $result      Result object to add messages to
     *
     * @return void
     */
    private function deltaDeletePerson(ApplicationEntity $application, $personId, Result $result)
    {
        try {
            /** @var ApplicationOrganisationPerson $appOrgPerson */
            $appOrgPerson = $this->getRepo('ApplicationOrganisationPerson')
                ->fetchForApplicationAndPerson($application->getId(), $personId);

            // It shouldn't be possible to delete a delete delta, but will cause issues
            if ($appOrgPerson->getAction() !== ApplicationOrganisationPerson::ACTION_DELETE) {
                $result->addMessage("ApplicationOrganisationPerson ID {$appOrgPerson->getId()} deleted");
                $this->getRepo('ApplicationOrganisationPerson')->delete($appOrgPerson);
                $this->getRepo('Person')->delete($appOrgPerson->getPerson());
            }
        } catch (\Dvsa\Olcs\Api\Domain\Exception\NotFoundException) {
            $appOrgPerson = new ApplicationOrganisationPerson(
                $application,
                $application->getLicence()->getOrganisation(),
                $this->getRepo()->getReference(\Dvsa\Olcs\Api\Entity\Person\Person::class, $personId)
            );
            $appOrgPerson->setAction(ApplicationOrganisationPerson::ACTION_DELETE);

            $application->addApplicationOrganisationPersons($appOrgPerson);
            $this->getRepo('ApplicationOrganisationPerson')->save($appOrgPerson);

            $result->addMessage("ApplicationOrganisationPerson ID {$appOrgPerson->getId()} delete delta created");
        }
    }

    /**
     * Delete an OrganisationPerson
     *
     * @param ApplicationEntity $application Application
     * @param int               $personId    ID of person to delete
     * @param Result            $result      Result object to add messages to
     *
     * @return void
     */
    private function deleteOrganisationPerson(ApplicationEntity $application, $personId, Result $result)
    {
        $organisationPersons = $this->getRepo('OrganisationPerson')->fetchListForOrganisationAndPerson(
            $application->getLicence()->getOrganisation()->getId(),
            $personId
        );

        // There should only be one, but just in case iterate them
        $organisationPersonIds = [];
        foreach ($organisationPersons as $organisationPerson) {
            $organisationPersonIds[] = $organisationPerson->getId();
        }
        $dto = \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList::create(['ids' => $organisationPersonIds]);
        $result->merge($this->handleSideEffect($dto));
    }
}
