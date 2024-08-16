<?php

/**
 * RestorePeople
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
 * RestorePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class RestorePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['ApplicationOrganisationPerson','Person'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Licence\DeletePeople */

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);
        $result = new Result();

        foreach ($command->getPersonIds() as $personId) {
            try {
                // attempt to restore a deleted person
                $appOrgPerson = $this->getRepo('ApplicationOrganisationPerson')
                    ->fetchForApplicationAndPerson($application->getId(), $personId);
                $result->addMessage("ApplicationOrganisationPerson ID {$appOrgPerson->getId()} deleted");

                $this->getRepo('ApplicationOrganisationPerson')->delete($appOrgPerson);
            } catch (\Dvsa\Olcs\Api\Domain\Exception\NotFoundException) {
                // attempt to restore an updated person
                $appOrgPerson = $this->getRepo('ApplicationOrganisationPerson')
                    ->fetchForApplicationAndOriginalPerson($application->getId(), $personId);
                $result->addMessage("ApplicationOrganisationPerson ID {$appOrgPerson->getId()} deleted");

                // as this is an Updated Delta, also remove the Person
                $this->getRepo('Person')->delete($appOrgPerson->getPerson());
                $this->getRepo('ApplicationOrganisationPerson')->delete($appOrgPerson);
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
}
