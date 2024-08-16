<?php

/**
 * Update Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Complaint;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Transfer\Command\Complaint\UpdateComplaint as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class UpdateComplaint extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Complaint';

    /**
     * Update complaint
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $complaint = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $person = $complaint->getComplainantContactDetails()->getPerson();
        $person = $this->updatePersonObject($command, $person);
        $complaint->getComplainantContactDetails()->setPerson($person);
        $result->addMessage('Person updated');

        $complaint = $this->updateComplaintObject($command, $complaint);

        $this->getRepo()->save($complaint);
        $result->addMessage('Complaint updated');

        return $result;
    }

    /**
     * @return Complaint
     */
    private function updateComplaintObject(Cmd $command, Complaint $complaint)
    {
        $complaint->setComplaintType($this->getRepo()->getRefdataReference($command->getComplaintType()));
        $complaint->setStatus($this->getRepo()->getRefdataReference($command->getStatus()));
        $complaint->setComplaintDate(new \DateTime($command->getComplaintDate()));

        if ($command->getClosedDate() !== null) {
            $complaint->setClosedDate(new \DateTime($command->getClosedDate()));
        }

        if ($command->getDescription() !== null) {
            $complaint->setDescription($command->getDescription());
        }

        if ($command->getDriverFamilyName() !== null) {
            $complaint->setDriverFamilyName($command->getDriverFamilyName());
        }

        if ($command->getDriverForename() !== null) {
            $complaint->setDriverForename($command->getDriverForename());
        }

        if ($command->getVrm() !== null) {
            $complaint->setVrm($command->getVrm());
        }

        return $complaint;
    }

    /**
     * @return Person
     */
    private function updatePersonObject(Cmd $command, Person $person)
    {
        if (
            $command->getComplainantForename() != $person->getForename() ||
            $command->getComplainantFamilyName() != $person->getFamilyName()
        ) {
            $person->setForename($command->getComplainantForename());
            $person->setFamilyName($command->getComplainantFamilyName());
        }

        return $person;
    }
}
