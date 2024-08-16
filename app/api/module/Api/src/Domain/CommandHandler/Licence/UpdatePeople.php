<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * UpdatePeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class UpdatePeople extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Person';
    protected $extraRepos = ['OrganisationPerson', 'Licence'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\UpdatePeople $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Repository\Person $repo */
        $repo = $this->getRepo();

        /** @var Entity\Person\Person $person */
        $person = $repo->fetchById(
            $command->getPerson(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        $person->updatePerson(
            $command->getForename(),
            $command->getFamilyName(),
            $repo->getRefdataReference($command->getTitle()),
            $command->getBirthDate()
        );
        $person->setOtherName($command->getOtherName());

        $repo->save($person);

        //  save position
        /** @var Entity\Licence\Licence $licence */
        $licence = $this->getRepo('Licence')->fetchUsingId($command);

        /** @var Repository\OrganisationPerson $orgPersonRepo */
        $orgPersonRepo = $this->getRepo('OrganisationPerson');

        $entities = $orgPersonRepo->fetchByOrgAndPerson($licence->getOrganisation(), $person);
        /** @var Entity\Organisation\OrganisationPerson $entity */
        foreach ($entities as $entity) {
            $entity->setPosition($command->getPosition());
            $orgPersonRepo->save($entity);
        }

        return $this->result
            ->addId('person', $person->getId())
            ->addMessage("Person ID {$person->getId()} updated");
    }
}
