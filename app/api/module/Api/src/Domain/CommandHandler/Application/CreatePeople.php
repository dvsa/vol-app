<?php

/**
 * CreatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * CreatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreatePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['OrganisationPerson', 'ApplicationOrganisationPerson', 'Person'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Application\CreatePeople */

        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($command);
        $result = new Result();

        // create a Person
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->updatePerson(
            $command->getForename(),
            $command->getFamilyName(),
            $this->getRepo()->getRefdataReference($command->getTitle()),
            $command->getBirthDate()
        );
        $person->setOtherName($command->getOtherName());
        $this->getRepo('Person')->save($person);

        if ($application->useDeltasInPeopleSection()) {
            $applicationPerson = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
                $application,
                $application->getLicence()->getOrganisation(),
                $person
            );
            $applicationPerson->setAction('A');
            $applicationPerson->setPosition($command->getPosition());

            $this->getRepo('ApplicationOrganisationPerson')->save($applicationPerson);
            $application->getApplicationOrganisationPersons()->add($applicationPerson);
            $result->addId('applicationOrganisationPerson', $applicationPerson->getId());
        } else {
            $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
            $organisationPerson->setOrganisation($application->getLicence()->getOrganisation());
            $organisationPerson->setPerson($person);
            $organisationPerson->setPosition($command->getPosition());

            $this->getRepo('OrganisationPerson')->save($organisationPerson);
            $application->getLicence()->getOrganisation()->getOrganisationPersons()->add($organisationPerson);
            $result->addId('organisatonPerson', $organisationPerson->getId());
        }

        $dtoData = ['id' => $application->getId(), 'section' => 'people'];
        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create($dtoData)
            )
        );

        $result->addMessage('Person added to Application');
        $result->addId('person', $person->getId());
        return $result;
    }
}
