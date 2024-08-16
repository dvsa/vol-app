<?php

/**
 * Create Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Impounding;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\CreateImpounding as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateImpounding extends AbstractImpounding implements TransactionedInterface
{
    /**
     * Handle command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $impounding = $this->createImpoundingObject($command);

        $this->getRepo()->save($impounding);
        $result->addMessage('Impounding created');
        $result->addId('impounding', $impounding->getId());

        // handle publish
        if ($command->getPublish() === 'Y') {
            $result->merge($this->handleSideEffect($this->createPublishCommand($impounding)));
        }

        return $result;
    }

    /**
     * @return Impounding
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createImpoundingObject(Cmd $command)
    {
        $impounding = new Impounding(
            $this->getRepo()->getReference(CasesEntity::class, $command->getCase()),
            $this->getRepo()->getRefdataReference($command->getImpoundingType())
        );

        $venue = $command->getVenue();
        if (!empty($venue) && $venue !== Impounding::VENUE_OTHER) {
            $venue = $this->getRepo()->getReference(VenueEntity::class, $command->getVenue());
        }
        $impounding->setVenueProperties(
            $venue,
            $command->getVenueOther()
        );

        $impoundingLegislationTypes = $this->generateImpoundingLegislationTypes(
            $command->getImpoundingLegislationTypes()
        );

        $impounding->setImpoundingLegislationTypes($impoundingLegislationTypes);

        if ($command->getApplicationReceiptDate() !== null) {
            $impounding->setApplicationReceiptDate(new \DateTime($command->getApplicationReceiptDate()));
        }

        $impounding->setVrm($command->getVrm());

        if ($command->getHearingDate() !== null) {
            $impounding->setHearingDate(new \DateTime($command->getHearingDate()));
        }

        if ($command->getPresidingTc() !== null) {
            $impounding->setPresidingTc($this->getRepo()->getRefdataReference($command->getPresidingTc()));
        }

        if ($command->getOutcome() !== null) {
            $impounding->setOutcome($this->getRepo()->getRefdataReference($command->getOutcome()));
        }

        if ($command->getOutcomeSentDate() !== null) {
            $impounding->setOutcomeSentDate(new \DateTime($command->getOutcomeSentDate()));
        }

        if ($command->getNotes() !== null) {
            $impounding->setNotes($command->getNotes());
        }

        return $impounding;
    }
}
