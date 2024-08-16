<?php

/**
 * Create Opposition
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Opposition;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition;
use Dvsa\Olcs\Api\Entity\Opposition\Opposer;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\Opposition\CreateOpposition as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Opposition
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateOpposition extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Opposition';

    protected $extraRepos = ['ContactDetails'];

    /**
     * Creates opposition  and associated entities
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $contactDetails = $this->createContactDetailsObject($command);
        $result->addMessage('Contact details created');

        $opposer = $this->createOpposerObject($command, $contactDetails);
        $result->addMessage('Opposer created');

        $opposition = $this->createOppositionObject($command, $opposer);
        $result->addMessage('Opposition created');

        $this->getRepo()->save($opposition);

        $result->addId('opposition ', $opposition->getId());
        $result->addId('opposer ', $opposer->getId());
        $result->addId('contactDetails', $contactDetails->getId());

        return $result;
    }

    private function createContactDetailsObject($command)
    {
        return ContactDetails::create(
            $this->getRepo()->getRefdataReference(ContactDetails::CONTACT_TYPE_OBJECTOR),
            $this->getRepo('ContactDetails')->populateRefDataReference(
                $command->getOpposerContactDetails()
            )
        );
    }

    /**
     * Create the opposition object
     *
     * @return Opposition
     */
    private function createOppositionObject(Cmd $command, Opposer $opposer)
    {
        $case = $this->getRepo()->getReference(Cases::class, $command->getCase());

        $opposition = new Opposition(
            $case,
            $opposer,
            $this->getRepo()->getRefdataReference($command->getOppositionType()),
            $this->getRepo()->getRefdataReference($command->getIsValid()),
            $command->getIsCopied(),
            $command->getIsInTime(),
            $command->getIsWillingToAttendPi(),
            $command->getIsWithdrawn()
        );

        if ($command->getRaisedDate() !== null) {
            $opposition->setRaisedDate(new \DateTime($command->getRaisedDate()));
        }

        if ($command->getIsValid() !== null) {
            $opposition->setIsValid($this->getRepo()->getRefdataReference($command->getIsValid()));
        }

        if ($command->getValidNotes() !== null) {
            $opposition->setValidNotes($command->getValidNotes());
        }

        if ($command->getStatus() !== null) {
            $opposition->setStatus($this->getRepo()->getRefdataReference($command->getStatus()));
        }

        $operatingCentres = $this->generateOperatingCentres($command);
        $opposition->setOperatingCentres($operatingCentres);

        if ($command->getGrounds() !== null) {
            $opposition->setGrounds($this->getRepo()->generateRefdataArrayCollection($command->getGrounds()));
        }

        if ($command->getNotes() !== null) {
            $opposition->setNotes($command->getNotes());
        }

        return $opposition;
    }

    /**
     * Create the opposer  object
     *
     * @return Opposer
     */
    private function createOpposerObject(Cmd $command, ContactDetails $contactDetails)
    {
        $opposer = new Opposer(
            $contactDetails,
            $this->getRepo()->getRefdataReference($command->getOpposerType()),
            $this->getRepo()->getRefdataReference($command->getOppositionType())
        );

        return $opposer;
    }

    /**
     * Generate list of operatingCentres based on type of opposition. At present it allows both types to specify OCs
     * This may need to be either one or the other.
     *
     * @return ArrayCollection
     */
    private function generateOperatingCentres(Cmd $command)
    {
        $collection = new ArrayCollection();

        $operatingCentres = $command->getOperatingCentres();

        if (!empty($operatingCentres)) {
            foreach ($operatingCentres as $oc) {
                $collection->add($this->getRepo()->getReference(OperatingCentre::class, $oc));
            }
        }

        return $collection;
    }
}
