<?php

/**
 * Update Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\UpdateImpounding as UpdateImpoundingCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;

/**
 * Update Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UpdateImpounding extends AbstractImpounding implements TransactionedInterface
{
    /**
     * Handle command
     *
     * @param CommandInterface|UpdateImpoundingCmd $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ImpoundingEntity $impounding */
        $repo = $this->getRepo();
        $impounding = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $impoundingType = $repo->getRefdataReference($command->getImpoundingType());

        $venue = $command->getVenue();

        if (!empty($venue) && $venue !== ImpoundingEntity::VENUE_OTHER) {
            $venue = $repo->getReference(VenueEntity::class, $command->getVenue());
        }

        $impoundingLegislationTypes = $this->generateImpoundingLegislationTypes(
            $command->getImpoundingLegislationTypes()
        );

        $impounding->update(
            $impoundingType,
            $impoundingLegislationTypes,
            $venue,
            $command->getVenueOther(),
            $command->getApplicationReceiptDate(),
            $command->getVrm(),
            $command->getHearingDate(),
            $this->refDataOrNull($command->getPresidingTc()),
            $this->refDataOrNull($command->getOutcome()),
            $command->getOutcomeSentDate(),
            $command->getNotes()
        );

        $repo->save($impounding);

        $result->addMessage('Impounding updated');

        // handle publish
        if ($command->getPublish() === 'Y') {
            $result->merge($this->handleSideEffect($this->createPublishCommand($impounding)));
        }

        return $result;
    }
}
