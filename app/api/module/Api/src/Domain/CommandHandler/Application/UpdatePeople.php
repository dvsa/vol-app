<?php

/**
 * UpdatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * UpdatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdatePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['Person', 'OrganisationPerson', 'ApplicationOrganisationPerson'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople */

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);
        $result = new Result();

        // load the person and checking its the right version
        $person = $this->getRepo('Person')->fetchById(
            $command->getPerson(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );
        $result->addId('person', $person->getId());

        if ($application->useDeltasInPeopleSection()) {
            try {
                $appOrgPerson = $this->getRepo('ApplicationOrganisationPerson')
                    ->fetchForApplicationAndPerson($application->getId(), $command->getPerson());
                $appOrgPerson->setPosition($command->getPosition());
                $this->getRepo('ApplicationOrganisationPerson')->save($appOrgPerson);

                $this->updatePersonFromCommand($person, $command);
                $this->getRepo('Person')->save($person);

                $result->addId('applicationOrganisationPerson', $appOrgPerson->getId());
                $result->addMessage("ApplicationOrganisationPerson updated");
            } catch (\Dvsa\Olcs\Api\Domain\Exception\NotFoundException) {
                // applicationOrganisationPerson was not found
                $newPerson = new \Dvsa\Olcs\Api\Entity\Person\Person();
                $this->updatePersonFromCommand($newPerson, $command);
                $this->getRepo('Person')->save($newPerson);

                $appOrgPerson = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
                    $application,
                    $application->getLicence()->getOrganisation(),
                    $newPerson
                );
                $appOrgPerson->setAction('U');
                $appOrgPerson->setOriginalPerson($person);
                $appOrgPerson->setPosition($command->getPosition());
                $this->getRepo('ApplicationOrganisationPerson')->save($appOrgPerson);

                $result->addId('applicationOrganisationPerson', $appOrgPerson->getId());
                // overwrite response person, with the new person
                $result->addId('person', $newPerson->getId());
                $result->addMessage("ApplicationOrganisationPerson created");
            }
        } else {
            $this->updatePersonFromCommand($person, $command);
            $this->getRepo('Person')->save($person);

            $orgPersons = $this->getRepo('OrganisationPerson')->fetchListForOrganisationAndPerson(
                $application->getLicence()->getOrganisation()->getId(),
                $command->getPerson()
            );
            // should only be one, but...
            foreach ($orgPersons as $orgPerson) {
                $orgPerson->setPosition($command->getPosition());
                $this->getRepo('OrganisationPerson')->save($orgPerson);

                $result->addId('organisationPerson', $orgPerson->getId());
            }

            $result->addMessage("OrganisationPerson updated");
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
     * Update a person entity with values from the command
     */
    private function updatePersonFromCommand(
        \Dvsa\Olcs\Api\Entity\Person\Person $person,
        \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople $command
    ) {
        $person->updatePerson(
            $command->getForename(),
            $command->getFamilyName(),
            $this->getRepo()->getRefdataReference($command->getTitle()),
            $command->getBirthDate()
        );
        $person->setOtherName($command->getOtherName());
    }
}
