<?php

/**
 * DeletePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * DeletePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeletePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['OrganisationPerson', 'Person'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\DeletePeople $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command \Dvsa\Olcs\Transfer\Command\Licence\DeletePeople */

        /* @var $licence LicenceEntity */
        $licence = $this->getRepo()->fetchUsingId($command);

        $result = $this->clearLicenceCacheSideEffect($licence->getId());

        foreach ($command->getPersonIds() as $personId) {
            $organisationPersons = $this->getRepo('OrganisationPerson')->fetchListForOrganisationAndPerson(
                $licence->getOrganisation()->getId(),
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

        return $result;
    }
}
