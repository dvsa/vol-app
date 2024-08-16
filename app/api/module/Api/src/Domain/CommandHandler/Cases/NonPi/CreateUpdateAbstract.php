<?php

/**
 * Create Update Abstract
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Repository\NonPi as Repository;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Update as UpdateCommand;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Create as CreateCommand;

/**
 * Create Update Abstract
 */
abstract class CreateUpdateAbstract extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'NonPi';

    /**
     * Sets data from a create or update hearing command
     *
     * @param Entities\Cases\Hearing                       $entity  hearing entity
     * @param CommandInterface|CreateCommand|UpdateCommand $command update or create command
     *
     * @return void
     */
    protected function setData(Entities\Cases\Hearing $entity, CommandInterface $command)
    {
        /** @var Repository $repo For traceability */
        $repo = $this->getRepo();

        // Hearing Type - required - no if needed.
        $entity->setHearingType($repo->getRefdataReference($command->getHearingType()));

        // Business logic?
        if ($command->getVenue() !== null) {
            $venue = $this->getRepo()->getReference(Entities\Venue::class, $command->getVenue());
            $entity->setVenue($venue);
            $entity->setVenueOther(null);
        } else {
            $entity->setVenueOther($command->getVenueOther());
            $entity->setVenue(null);
        }

        $case = $this->getRepo()->getReference(Entities\Cases\Cases::class, $command->getCase());
        $entity->setCase($case);

        $entity->setAgreedByTcDate($entity->processDate($command->getAgreedByTcDate()));
        $entity->setHearingDate($entity->processDate($command->getHearingDate(), \DateTime::ISO8601, false));

        //deals with witnesses field being null or empty string
        $witnesses = is_numeric($command->getWitnessCount()) ? $command->getWitnessCount() : 0;
        $entity->setWitnessCount($witnesses);

        $cmdOutcome = $command->getOutcome();
        $outcome = $cmdOutcome === null ? null : $repo->getRefdataReference($cmdOutcome);
        $entity->setOutcome($outcome);

        $entity->setPresidingStaffName($command->getPresidingStaffName());
    }
}
