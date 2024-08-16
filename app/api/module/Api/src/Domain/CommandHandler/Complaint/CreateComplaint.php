<?php

/**
 * Create Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Complaint;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Transfer\Command\Complaint\CreateComplaint as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateComplaint extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Complaint';

    /**
     * Creates complaint and associated entities
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $person = $this->createPersonObject($command);
        $result->addMessage('Person created');

        $contactDetails = $this->createContactDetailsObject($person);
        $result->addMessage('Contact details created');

        $complaint = $this->createComplaintObject($command, $contactDetails);

        $this->getRepo()->save($complaint);
        $result->addMessage('Complaint created');

        $result->addId('complaint', $complaint->getId());
        $result->addId('person', $person->getId());
        $result->addId('contactDetails', $contactDetails->getId());

        return $result;
    }

    /**
     * Create the complaint object
     *
     * @return Complaint
     */
    private function createComplaintObject(Cmd $command, ContactDetails $contactDetails)
    {
        $isCompliance = true;

        $complaint = new Complaint(
            $this->getRepo()->getReference(Cases::class, $command->getCase()),
            $isCompliance,
            $this->getRepo()->getRefdataReference($command->getStatus()),
            new \DateTime($command->getComplaintDate()),
            $contactDetails
        );
        $complaint->setComplaintType($this->getRepo()->getRefdataReference($command->getComplaintType()));

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
     * Create person object
     * @return Person
     */
    private function createPersonObject(Cmd $command)
    {
        $person = new Person();

        $person->setForename($command->getComplainantForename());
        $person->setFamilyName($command->getComplainantFamilyName());

        return $person;
    }

    /**
     * Create ContactDetails object
     * @return ContactDetails
     */
    private function createContactDetailsObject(Person $person)
    {
        $contactDetails = new ContactDetails(
            $this->getRepo()->getRefdataReference(
                ContactDetails::CONTACT_TYPE_COMPLAINANT
            )
        );

        $contactDetails->setPerson($person);

        return $contactDetails;
    }
}
