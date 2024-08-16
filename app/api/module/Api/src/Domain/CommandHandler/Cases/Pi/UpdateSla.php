<?php

/**
 * Update sla
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateSla as UpdateSlaCmd;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Doctrine\ORM\Query;

/**
 * Update sla
 */
final class UpdateSla extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Pi';

    /**
     * Update pi decision
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateSlaCmd $command */
        $result = new Result();

        $writtenOutcome = $command->getWrittenOutcome();

        /** @var PiEntity $pi */
        $pi = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        match ($writtenOutcome) {
            SlaEntity::VERBAL_DECISION_ONLY => $pi->updateWrittenOutcomeVerbal(
                $this->getRepo()->getRefdataReference($writtenOutcome),
                $command->getCallUpLetterDate(),
                $command->getBriefToTcDate(),
                $command->getDecisionLetterSentDate()
            ),
            SlaEntity::WRITTEN_OUTCOME_DECISION => $pi->updateWrittenOutcomeDecision(
                $this->getRepo()->getRefdataReference($writtenOutcome),
                $command->getCallUpLetterDate(),
                $command->getBriefToTcDate(),
                $command->getTcWrittenDecisionDate(),
                $command->getWrittenDecisionLetterDate()
            ),
            SlaEntity::WRITTEN_OUTCOME_REASON => $pi->updateWrittenOutcomeReason(
                $this->getRepo()->getRefdataReference($writtenOutcome),
                $command->getCallUpLetterDate(),
                $command->getBriefToTcDate(),
                $command->getTcWrittenReasonDate(),
                $command->getWrittenReasonLetterDate()
            ),
            default => $pi->updateWrittenOutcomeNone(
                null,
                $command->getCallUpLetterDate(),
                $command->getBriefToTcDate()
            ),
        };

        $this->getRepo()->save($pi);
        $result->addMessage('Sla updated');
        $result->addId('Pi', $pi->getId());

        // generate all related SLA Target Dates
        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDateCmd::create(
                    [
                        'pi' => $pi->getId()
                    ]
                )
            )
        );

        return $result;
    }
}
