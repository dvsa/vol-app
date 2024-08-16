<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity;

/**
 * CreatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreatePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['OrganisationPerson', 'Person'];

    /**
     * Handle Command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\CreatePeople $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $licence LicenceEntity */
        $licence = $this->getRepo()->fetchUsingId($command);

        //  save person
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->updatePerson(
            $command->getForename(),
            $command->getFamilyName(),
            $this->getRepo()->getRefdataReference($command->getTitle()),
            $command->getBirthDate()
        );
        $person->setOtherName($command->getOtherName());

        /** @var Repository\Person $personRepo */
        $personRepo = $this->getRepo('Person');
        $personRepo->save($person);

        //  save organisation person relation
        $organisationPerson = new Entity\Organisation\OrganisationPerson();
        $organisationPerson
            ->setOrganisation($licence->getOrganisation())
            ->setPerson($person)
            ->setPosition($command->getPosition());

        /** @var Repository\OrganisationPerson $orgPersonRepo */
        $orgPersonRepo = $this->getRepo('OrganisationPerson');
        $orgPersonRepo->save($organisationPerson);

        $this->result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        return $this->result
            ->addMessage('OrganisationPerson created')
            ->addId('organisationPerson', $organisationPerson->getId())
            ->addId('person', $person->getId());
    }
}
