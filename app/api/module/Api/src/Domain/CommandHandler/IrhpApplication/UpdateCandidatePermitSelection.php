<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\AcceptScoringFeeCreationTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCandidatePermitSelection as UpdateCandidatePermitSelectionCmd;

/**
 * Update candidate permit selection
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UpdateCandidatePermitSelection extends AbstractCommandHandler implements TransactionedInterface
{
    use AcceptScoringFeeCreationTrait;

    public const ERR_CANT_SELECT_CANDIDATE_PERMITS = 'canSelectCandidatePermits is not true';

    public const ERR_NO_PERMITS_WANTED = 'No permits specified as wanted from available candidate permits';

    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpCandidatePermit', 'FeeType'];

    /**
     * Handle command
     *
     * @param UpdateCandidatePermitSelectionCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();
        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);

        if (!$irhpApplication->canSelectCandidatePermits()) {
            throw new ForbiddenException(self::ERR_CANT_SELECT_CANDIDATE_PERMITS);
        }

        $irhpCandidatePermitRepo = $this->getRepo('IrhpCandidatePermit');
        $candidatePermits = $irhpApplication->getFirstIrhpPermitApplication()->getSuccessfulIrhpCandidatePermits();
        $wantedCandidatePermitIds = $command->getSelectedCandidatePermitIds();

        $wantedCount = 0;
        foreach ($candidatePermits as $candidatePermit) {
            $wanted = in_array(
                $candidatePermit->getId(),
                $wantedCandidatePermitIds
            );

            if ($wanted) {
                $wantedCount++;
            }
        }

        if ($wantedCount == 0) {
            throw new ForbiddenException(self::ERR_NO_PERMITS_WANTED);
        }

        foreach ($candidatePermits as $candidatePermit) {
            $wanted = in_array(
                $candidatePermit->getId(),
                $wantedCandidatePermitIds
            );

            $candidatePermit->updateWanted($wanted);
            $irhpCandidatePermitRepo->save($candidatePermit);
        }

        $feeCommands = [];

        $outstandingFees = $irhpApplication->getOutstandingFees();
        foreach ($outstandingFees as $fee) {
            $feeCommands[] = CancelFee::create(['id' => $fee->getId()]);
        }

        $feeCommands[] = $this->getCreateIssueFeeCommand($irhpApplication, $wantedCount);

        $this->result->merge(
            $this->handleSideEffects($feeCommands)
        );

        return $this->result;
    }
}
