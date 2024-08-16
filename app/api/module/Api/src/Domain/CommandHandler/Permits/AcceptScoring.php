<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\AcceptScoring as AcceptScoringCmd;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringResult;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\AcceptScoringFeeCreationTrait;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\Traits\ApplicationAcceptConsts;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Query\Permits\GetScoredPermitList;
use Exception;

/**
 * Accept scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AcceptScoring extends AbstractCommandHandler
{
    use QueueAwareTrait;
    use AcceptScoringFeeCreationTrait;

    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitStock', 'FeeType', 'SystemParameter'];

    /**
     * Handle command
     *
     * @param AcceptScoringCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $stockId = $command->getId();

        $stockRepo = $this->getRepo('IrhpPermitStock');
        $stock = $stockRepo->fetchById($stockId);
        $stockRepo->refresh($stock);

        if ($stock->statusAllowsAcceptScoring()) {
            $prerequisiteResult = $this->handleQuery(
                CheckAcceptScoringAndPostScoringReportPrerequisites::create(['id' => $stockId])
            );
        } else {
            $prerequisiteResult = [
                'result' => false,
                'message' => sprintf(
                    'Accept scoring is not permitted when stock status is \'%s\'',
                    $stock->getStatusDescription()
                )
            ];
        }

        if (!$prerequisiteResult['result']) {
            $stock->proceedToAcceptPrerequisiteFail($this->refData(IrhpPermitStock::STATUS_ACCEPT_PREREQUISITE_FAIL));
            $stockRepo->save($stock);

            $this->result->addMessage('Prerequisite failed: ' . $prerequisiteResult['message']);
            return $this->result;
        }

        $stock->proceedToAcceptInProgress($this->refData(IrhpPermitStock::STATUS_ACCEPT_IN_PROGRESS));
        $stockRepo->save($stock);

        try {
            $scoringResults = $this->handleQuery(
                GetScoredPermitList::create(['stockId' => $stockId])
            );

            $this->result->merge(
                $this->handleSideEffect(
                    UploadScoringResult::create([
                        'csvContent' => $scoringResults['result'],
                        'fileDescription' => 'Accepted Scoring Results'
                    ])
                )
            );

            $applicationIds = $this->getRepo('IrhpApplication')->fetchInScopeUnderConsiderationApplicationIds($stockId);

            $this->result->addMessage(
                sprintf('%d under consideration applications found', count($applicationIds))
            );

            foreach ($applicationIds as $applicationId) {
                $this->processApplication(
                    $this->getRepo('IrhpApplication')->fetchById($applicationId)
                );
            }

            $stock->proceedToAcceptSuccessful($this->refData(IrhpPermitStock::STATUS_ACCEPT_SUCCESSFUL));
            $this->result->addMessage('Acceptance process completed successfully.');
        } catch (Exception $e) {
            $stock->proceedToAcceptUnexpectedFail($this->refData(IrhpPermitStock::STATUS_ACCEPT_UNEXPECTED_FAIL));
            $this->result->addMessage('Acceptance process failed: ' . $e->getMessage());
        }

        $stockRepo->save($stock);
        return $this->result;
    }

    /**
     * Send outcome notification and create fees as required for an application
     *
     *
     * @throws Exception
     */
    private function processApplication(IrhpApplication $irhpApplication)
    {
        $this->result->addMessage(
            sprintf('processing application with id %d:', $irhpApplication->getId())
        );

        $applicationAwardedPermits = $irhpApplication->getSuccessLevel() != ApplicationAcceptConsts::SUCCESS_LEVEL_NONE;
        $applicationChecked = $irhpApplication->getChecked();

        if ($applicationAwardedPermits && !$applicationChecked) {
            $this->result->addMessage('- application has been awarded permits and has not been checked, skipping');
            return;
        }

        $outcomeNotificationType = $irhpApplication->getOutcomeNotificationType();
        match ($outcomeNotificationType) {
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL => $this->triggerEmailNotification($irhpApplication),
            ApplicationAcceptConsts::NOTIFICATION_TYPE_MANUAL => $this->triggerManualNotification($irhpApplication),
            default => throw new Exception('Unknown notification type: ' . $outcomeNotificationType),
        };

        $this->createApplicationFeesAndUpdateStatus($irhpApplication);
        $this->getRepo('IrhpApplication')->save($irhpApplication);
    }

    /**
     * Send the outcome notification for an application requiring manual notification
     */
    private function triggerManualNotification(IrhpApplication $irhpApplication)
    {
        $this->result->merge(
            $this->handleSideEffect(
                $this->getCreateTaskCommand($irhpApplication)
            )
        );
    }

    /**
     * Send the outcome notification for an application requiring email notification
     */
    private function triggerEmailNotification(IrhpApplication $irhpApplication)
    {
        $successLevel = $irhpApplication->getSuccessLevel();

        if (
            $successLevel === ApplicationAcceptConsts::SUCCESS_LEVEL_NONE
            && $this->getRepo('SystemParameter')->fetchValue(SystemParameter::DISABLE_ECMT_ALLOC_EMAIL_NONE)
        ) {
            $this->result->addMessage(sprintf('- email sending disabled for %s', $successLevel));
            return;
        }

        $emailCommandLookup = $irhpApplication->getEmailCommandLookup();
        $emailCommand = $emailCommandLookup[$successLevel];

        $this->result->addMessage(
            sprintf('- sending email using command %s', $emailCommand)
        );

        $irhpApplicationId = $irhpApplication->getId();

        $this->result->merge(
            $this->handleSideEffect(
                $this->emailQueue(
                    $emailCommand,
                    [ 'id' => $irhpApplicationId ],
                    $irhpApplicationId
                )
            )
        );
    }

    /**
     * Create any applicable fees for an application and update the status accordingly
     */
    private function createApplicationFeesAndUpdateStatus(IrhpApplication $irhpApplication)
    {
        if ($irhpApplication->getSuccessLevel() == ApplicationAcceptConsts::SUCCESS_LEVEL_NONE) {
            $this->result->addMessage('- no fee applicable, set application to unsuccessful');

            $irhpApplication->proceedToUnsuccessful(
                $this->refData(IrhpInterface::STATUS_UNSUCCESSFUL)
            );

            return;
        }

        $this->result->merge(
            $this->handleSideEffect(
                $this->getCreateIssueFeeCommand(
                    $irhpApplication,
                    $irhpApplication->getPermitsAwarded()
                )
            )
        );

        $this->result->addMessage('- create fee and set application to awaiting fee');

        $irhpApplication->proceedToAwaitingFee(
            $this->refData(IrhpInterface::STATUS_AWAITING_FEE)
        );
    }

    /**
     * Get task creation command for an application
     *
     *
     * @return CreateTask
     */
    private function getCreateTaskCommand(IrhpApplication $irhpApplication)
    {
        return CreateTask::create(
            [
                'category' => Task::CATEGORY_PERMITS,
                'subCategory' => Task::SUBCATEGORY_PERMITS_APPLICATION_OUTCOME,
                'description' => Task::TASK_DESCRIPTION_SEND_OUTCOME_LETTER,
                'irhpApplication' => $irhpApplication->getId(),
                'licence' => $irhpApplication->getLicence()->getId()
            ]
        );
    }
}
