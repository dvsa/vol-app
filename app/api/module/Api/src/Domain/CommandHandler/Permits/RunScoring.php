<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\RunScoring as RunScoringCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckRunScoringPrerequisites;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulDaPermitApplications;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulSectorPermitApplications;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulRemainingPermitApplications;
use Dvsa\Olcs\Cli\Domain\Command\Permits\ApplyRangesToSuccessfulPermitApplications;
use Dvsa\Olcs\Cli\Domain\Command\Permits\InitialiseScope;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Query\Permits\GetScoredPermitList;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringResult;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringLog;
use Exception;
use Olcs\Logging\Log\Logger;

/**
 * Run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RunScoring extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    /** @var int */
    private $stockId;

    /**
     * Handle command
     *
     * @param RunScoringCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->stockId = $command->getId();

        $stockRepo = $this->getRepo();
        $stock = $stockRepo->fetchById($this->stockId);
        $stockRepo->refresh($stock);

        if ($stock->statusAllowsRunScoring()) {
            $prerequisiteResult = $this->handleQuery(
                CheckRunScoringPrerequisites::create(['id' => $this->stockId])
            );
        } else {
            $prerequisiteResult = [
                'result' => false,
                'message' => sprintf(
                    'Run scoring is not permitted when stock status is \'%s\'',
                    $stock->getStatusDescription()
                )
            ];
        }

        if (!$prerequisiteResult['result']) {
            $stock->proceedToScoringPrerequisiteFail($this->refData(IrhpPermitStock::STATUS_SCORING_PREREQUISITE_FAIL));
            $stockRepo->save($stock);

            $this->result->addMessage('Prerequisite failed: ' . $prerequisiteResult['message']);
            return $this->result;
        }

        $stock->proceedToScoringInProgress($this->refData(IrhpPermitStock::STATUS_SCORING_IN_PROGRESS));
        $stockRepo->save($stock);

        $stockIdParams = ['stockId' => $this->stockId];

        $initialiseScopeParams = $stockIdParams;
        $initialiseScopeParams['deviation'] = $command->getDeviation();

        try {
            $this->result->merge(
                $this->handleSideEffects(
                    [
                        InitialiseScope::create($initialiseScopeParams),
                        MarkSuccessfulSectorPermitApplications::create($stockIdParams),
                        MarkSuccessfulDaPermitApplications::create($stockIdParams),
                        MarkSuccessfulRemainingPermitApplications::create($stockIdParams),
                        ApplyRangesToSuccessfulPermitApplications::create($stockIdParams),
                    ]
                )
            );

            $scoringResults = $this->handleQuery(
                GetScoredPermitList::create($stockIdParams)
            );

            $this->result->merge(
                $this->handleSideEffect(
                    UploadScoringResult::create([
                        'csvContent' => $scoringResults['result'],
                        'fileDescription' => 'Scoring Results'
                    ])
                )
            );

            $stock->proceedToScoringSuccessful($this->refData(IrhpPermitStock::STATUS_SCORING_SUCCESSFUL));
            $this->result->addMessage('Scoring process completed successfully.');
        } catch (Exception $e) {
            $stock->proceedToScoringUnexpectedFail($this->refData(IrhpPermitStock::STATUS_SCORING_UNEXPECTED_FAIL));
            $this->result->addMessage('Scoring process failed: ' . $e->getMessage());
        }

        try {
            $logOutput = implode("\r\n", $this->result->getMessages());
            $this->result->merge(
                $this->handleSideEffect(
                    UploadScoringLog::create(['logContent' => $logOutput])
                )
            );
        } catch (Exception) {
            Logger::err('Unable to update status/write scoring log file for stock id ' . $this->stockId);
        }

        $stockRepo->save($stock);
        return $this->result;
    }
}
