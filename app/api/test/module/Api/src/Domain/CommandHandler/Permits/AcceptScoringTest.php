<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Repository\FeeType;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringResult;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgUnsuccessful;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPartSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\AcceptScoring;
use Dvsa\Olcs\Api\Domain\Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites;
use Dvsa\Olcs\Api\Domain\Query\Permits\GetScoredPermitList;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\Traits\ApplicationAcceptConsts;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SystemParameterEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class AcceptScoringTest extends AbstractCommandHandlerTestCase
{
    private $issueFeeProductReference = 'ISSUE_FEE_PRODUCT_REFERENCE';

    private $emailCommandLookup;

    public function setUp(): void
    {
        $this->mockRepo('FeeType', FeeType::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStock::class);
        $this->mockRepo('SystemParameter', SystemParameter::class);
        $this->mockRepo('IrhpApplication', IrhpApplication::class);

        $this->sut = m::mock(AcceptScoring::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->emailCommandLookup = [
            ApplicationAcceptConsts::SUCCESS_LEVEL_NONE => SendEcmtApsgUnsuccessful::class,
            ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL => SendEcmtApsgPartSuccessful::class,
            ApplicationAcceptConsts::SUCCESS_LEVEL_FULL => SendEcmtApsgSuccessful::class
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_AWAITING_FEE,
            IrhpInterface::STATUS_UNSUCCESSFUL,
            IrhpPermitStockEntity::STATUS_ACCEPT_PREREQUISITE_FAIL,
            IrhpPermitStockEntity::STATUS_ACCEPT_IN_PROGRESS,
            IrhpPermitStockEntity::STATUS_ACCEPT_SUCCESSFUL,
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $disableEcmtAllocEmailNone, mixed $expected): void
    {
        $stockId = 47;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($stockId);

        $feeTypeId = 4;
        $feeTypeDescription = 'Issue Fee';
        $feeTypeFixedValue = 4;

        $this->repoMap['SystemParameter']->shouldReceive('fetchValue')
            ->with(SystemParameterEntity::DISABLE_ECMT_ALLOC_EMAIL_NONE)
            ->andReturn($disableEcmtAllocEmailNone);

        $feeType = m::mock(FeeTypeEntity::class);
        $feeType->shouldReceive('getId')
            ->andReturn($feeTypeId);
        $feeType->shouldReceive('getDescription')
            ->andReturn($feeTypeDescription);
        $feeType->shouldReceive('getFixedValue')
            ->andReturn($feeTypeFixedValue);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($this->issueFeeProductReference)
            ->andReturn($feeType);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('statusAllowsAcceptScoring')
            ->andReturn(true);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitStock']->shouldReceive('refresh')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitStock->shouldReceive('proceedToAcceptInProgress')
            ->with($this->refData[IrhpPermitStockEntity::STATUS_ACCEPT_IN_PROGRESS])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitStock']->shouldReceive('save')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $manualNotificationApplicationId = 1;
        $manualNotificationApplicationLicenceId = 2;
        $manualNotificationApplicationPermitsAwarded = 3;
        $manualNotificationApplication = $this->getIrhpApplicationMock(
            $manualNotificationApplicationId,
            $manualNotificationApplicationLicenceId,
            $manualNotificationApplicationPermitsAwarded,
            ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_MANUAL,
            true
        );

        $manualNotificationApplication->shouldReceive('proceedToAwaitingFee')
            ->with($this->refData[IrhpInterface::STATUS_AWAITING_FEE])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($manualNotificationApplication)
            ->once()
            ->globally()
            ->ordered();

        $checkedSuccessfulApplicationId = 2;
        $checkedSuccessfulApplicationLicenceId = 30;
        $checkedSuccessfulApplicationPermitsAwarded = 10;
        $checkedSuccessfulApplication = $this->getIrhpApplicationMock(
            $checkedSuccessfulApplicationId,
            $checkedSuccessfulApplicationLicenceId,
            $checkedSuccessfulApplicationPermitsAwarded,
            ApplicationAcceptConsts::SUCCESS_LEVEL_FULL,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            true
        );

        $checkedSuccessfulApplication->shouldReceive('proceedToAwaitingFee')
            ->with($this->refData[IrhpInterface::STATUS_AWAITING_FEE])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($checkedSuccessfulApplication)
            ->once()
            ->globally()
            ->ordered();

        $uncheckedSuccessfulApplicationId = 52;
        $uncheckedSuccessfulApplicationLicenceId = 80;
        $uncheckedSuccessfulApplicationPermitsAwarded = 11;
        $uncheckedSuccessfulApplication = $this->getIrhpApplicationMock(
            $uncheckedSuccessfulApplicationId,
            $uncheckedSuccessfulApplicationLicenceId,
            $uncheckedSuccessfulApplicationPermitsAwarded,
            ApplicationAcceptConsts::SUCCESS_LEVEL_FULL,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            false
        );

        $checkedPartSuccessfulApplicationId = 3;
        $checkedPartSuccessfulApplicationLicenceId = 71;
        $checkedPartSuccessfulApplicationPermitsAwarded = 5;
        $checkedPartSuccessfulApplication = $this->getIrhpApplicationMock(
            $checkedPartSuccessfulApplicationId,
            $checkedPartSuccessfulApplicationLicenceId,
            $checkedPartSuccessfulApplicationPermitsAwarded,
            ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            true
        );

        $checkedPartSuccessfulApplication->shouldReceive('proceedToAwaitingFee')
            ->with($this->refData[IrhpInterface::STATUS_AWAITING_FEE])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($checkedPartSuccessfulApplication)
            ->once()
            ->globally()
            ->ordered();

        $uncheckedPartSuccessfulApplicationId = 53;
        $uncheckedPartSuccessfulApplicationLicenceId = 81;
        $uncheckedPartSuccessfulApplicationPermitsAwarded = 7;
        $uncheckedPartSuccessfulApplication = $this->getIrhpApplicationMock(
            $uncheckedPartSuccessfulApplicationId,
            $uncheckedPartSuccessfulApplicationLicenceId,
            $uncheckedPartSuccessfulApplicationPermitsAwarded,
            ApplicationAcceptConsts::SUCCESS_LEVEL_PARTIAL,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            false
        );

        $checkedUnsuccessfulApplicationId = 4;
        $checkedUnsuccessfulApplication = $this->getIrhpApplicationMock(
            $checkedUnsuccessfulApplicationId,
            46,
            0,
            ApplicationAcceptConsts::SUCCESS_LEVEL_NONE,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            true
        );

        $checkedUnsuccessfulApplication->shouldReceive('proceedToUnsuccessful')
            ->with($this->refData[IrhpInterface::STATUS_UNSUCCESSFUL])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($checkedUnsuccessfulApplication)
            ->once()
            ->globally()
            ->ordered();

        $uncheckedUnsuccessfulApplicationId = 54;
        $uncheckedUnsuccessfulApplication = $this->getIrhpApplicationMock(
            $uncheckedUnsuccessfulApplicationId,
            76,
            0,
            ApplicationAcceptConsts::SUCCESS_LEVEL_NONE,
            ApplicationAcceptConsts::NOTIFICATION_TYPE_EMAIL,
            false
        );

        $uncheckedUnsuccessfulApplication->shouldReceive('proceedToUnsuccessful')
            ->with($this->refData[IrhpInterface::STATUS_UNSUCCESSFUL])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($uncheckedUnsuccessfulApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->sut->shouldReceive('handleQuery')->once()
            ->with(m::type(CheckAcceptScoringAndPostScoringReportPrerequisites::class))
            ->andReturnUsing(function ($query) use ($stockId) {
                $this->assertEquals($stockId, $query->getId());

                return [
                    'result' => true,
                    'message' => 'Accept scoring permitted'
                ];
            });

        $this->sut->shouldReceive('handleQuery')->once()
            ->with(m::type(GetScoredPermitList::class))
            ->andReturnUsing(function ($query) use ($stockId) {
                $this->assertEquals($stockId, $query->getStockId());

                return [
                    'result' => [],
                ];
            });

        $irhpPermitStock->shouldReceive('proceedToAcceptSuccessful')
            ->with($this->refData[IrhpPermitStockEntity::STATUS_ACCEPT_SUCCESSFUL])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitStock']->shouldReceive('save')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($manualNotificationApplicationId)
            ->andReturn($manualNotificationApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($checkedSuccessfulApplicationId)
            ->andReturn($checkedSuccessfulApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($uncheckedSuccessfulApplicationId)
            ->andReturn($uncheckedSuccessfulApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($checkedPartSuccessfulApplicationId)
            ->andReturn($checkedPartSuccessfulApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($uncheckedPartSuccessfulApplicationId)
            ->andReturn($uncheckedPartSuccessfulApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($checkedUnsuccessfulApplicationId)
            ->andReturn($checkedUnsuccessfulApplication);
        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($uncheckedUnsuccessfulApplicationId)
            ->andReturn($uncheckedUnsuccessfulApplication);

        $taskResult = new Result();

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $manualNotificationApplicationLicenceId,
                'irhpApplication' => $manualNotificationApplicationId,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Issue Fee - 3 permits',
                'feeType' => $feeTypeId,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'amount' => 12,
            ],
            $taskResult
        );

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $checkedSuccessfulApplicationLicenceId,
                'irhpApplication' => $checkedSuccessfulApplicationId,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Issue Fee - 10 permits',
                'feeType' => $feeTypeId,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'amount' => 40,
            ],
            $taskResult
        );

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $checkedPartSuccessfulApplicationLicenceId,
                'irhpApplication' => $checkedPartSuccessfulApplicationId,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Issue Fee - 5 permits',
                'feeType' => $feeTypeId,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'amount' => 20,
            ],
            $taskResult
        );

        $this->expectedSideEffect(
            UploadScoringResult::class,
            [
                'csvContent' => [],
                'fileDescription' => 'Accepted Scoring Results'
            ],
            $taskResult
        );

        $taskParams = [
            'category' => TaskEntity::CATEGORY_PERMITS,
            'subCategory' => TaskEntity::SUBCATEGORY_PERMITS_APPLICATION_OUTCOME,
            'description' => 'Send outcome letter',
            'irhpApplication' => $manualNotificationApplicationId,
            'licence' => $manualNotificationApplicationLicenceId
        ];
        $this->expectedSideEffect(CreateTask::class, $taskParams, $taskResult);

        $this->expectedEmailQueueSideEffect(
            SendEcmtApsgSuccessful::class,
            ['id' => $checkedSuccessfulApplicationId],
            $checkedSuccessfulApplicationId,
            $taskResult
        );

        $this->expectedEmailQueueSideEffect(
            SendEcmtApsgPartSuccessful::class,
            ['id' => $checkedPartSuccessfulApplicationId],
            $checkedPartSuccessfulApplicationId,
            $taskResult
        );

        if (!$disableEcmtAllocEmailNone) {
            $this->expectedEmailQueueSideEffect(
                SendEcmtApsgUnsuccessful::class,
                ['id' => $checkedUnsuccessfulApplicationId],
                $checkedUnsuccessfulApplicationId,
                $taskResult
            );

            $this->expectedEmailQueueSideEffect(
                SendEcmtApsgUnsuccessful::class,
                ['id' => $uncheckedUnsuccessfulApplicationId],
                $uncheckedUnsuccessfulApplicationId,
                $taskResult
            );
        }

        $this->repoMap['IrhpApplication']->shouldReceive('fetchInScopeUnderConsiderationApplicationIds')
            ->with($stockId)
            ->andReturn(
                [
                    $manualNotificationApplicationId,
                    $checkedSuccessfulApplicationId,
                    $uncheckedSuccessfulApplicationId,
                    $checkedPartSuccessfulApplicationId,
                    $uncheckedPartSuccessfulApplicationId,
                    $checkedUnsuccessfulApplicationId,
                    $uncheckedUnsuccessfulApplicationId
                ]
            );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommand(): array
    {
        return [
            'unsuccessful email enabled' => [
                'disableEcmtAllocEmailNone' => 0,
                'expected' => [
                    'id' => [],
                    'messages' => [
                        '7 under consideration applications found',
                        'processing application with id 1:',
                        '- create fee and set application to awaiting fee',
                        'processing application with id 2:',
                        '- sending email using command ' . SendEcmtApsgSuccessful::class,
                        '- create fee and set application to awaiting fee',
                        'processing application with id 52:',
                        '- application has been awarded permits and has not been checked, skipping',
                        'processing application with id 3:',
                        '- sending email using command ' . SendEcmtApsgPartSuccessful::class,
                        '- create fee and set application to awaiting fee',
                        'processing application with id 53:',
                        '- application has been awarded permits and has not been checked, skipping',
                        'processing application with id 4:',
                        '- sending email using command ' . SendEcmtApsgUnsuccessful::class,
                        '- no fee applicable, set application to unsuccessful',
                        'processing application with id 54:',
                        '- sending email using command ' . SendEcmtApsgUnsuccessful::class,
                        '- no fee applicable, set application to unsuccessful',
                        'Acceptance process completed successfully.',
                    ],
                ],
            ],
            'unsuccessful email disabled' => [
                'disableEcmtAllocEmailNone' => 1,
                'expected' => [
                    'id' => [],
                    'messages' => [
                        '7 under consideration applications found',
                        'processing application with id 1:',
                        '- create fee and set application to awaiting fee',
                        'processing application with id 2:',
                        '- sending email using command ' . SendEcmtApsgSuccessful::class,
                        '- create fee and set application to awaiting fee',
                        'processing application with id 52:',
                        '- application has been awarded permits and has not been checked, skipping',
                        'processing application with id 3:',
                        '- sending email using command ' . SendEcmtApsgPartSuccessful::class,
                        '- create fee and set application to awaiting fee',
                        'processing application with id 53:',
                        '- application has been awarded permits and has not been checked, skipping',
                        'processing application with id 4:',
                        '- email sending disabled for ' . ApplicationAcceptConsts::SUCCESS_LEVEL_NONE,
                        '- no fee applicable, set application to unsuccessful',
                        'processing application with id 54:',
                        '- email sending disabled for ' . ApplicationAcceptConsts::SUCCESS_LEVEL_NONE,
                        '- no fee applicable, set application to unsuccessful',
                        'Acceptance process completed successfully.',
                    ],
                ],
            ],
        ];
    }

    public function testIncorrectStockStatus(): void
    {
        $stockId = 35;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($stockId);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('statusAllowsAcceptScoring')
            ->andReturn(false);
        $irhpPermitStock->shouldReceive('getStatusDescription')
            ->andReturn('Stock scoring never run');

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitStock']->shouldReceive('refresh')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitStock->shouldReceive('proceedToAcceptPrerequisiteFail')
            ->with($this->refData[IrhpPermitStockEntity::STATUS_ACCEPT_PREREQUISITE_FAIL])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitStock']->shouldReceive('save')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Prerequisite failed: Accept scoring is not permitted when stock status is \'Stock scoring never run\''],
            $result->getMessages()
        );
    }

    public function testPrerequisitesFail(): void
    {
        $stockId = 35;

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($stockId);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('statusAllowsAcceptScoring')
            ->andReturn(true);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitStock']->shouldReceive('refresh')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitStock->shouldReceive('proceedToAcceptPrerequisiteFail')
            ->with($this->refData[IrhpPermitStockEntity::STATUS_ACCEPT_PREREQUISITE_FAIL])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitStock']->shouldReceive('save')
            ->with($irhpPermitStock)
            ->once()
            ->globally()
            ->ordered();

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(CheckAcceptScoringAndPostScoringReportPrerequisites::class))
            ->andReturnUsing(function ($query) use ($stockId) {
                $this->assertEquals($stockId, $query->getId());

                return [
                    'result' => false,
                    'message' => 'Accept scoring not permitted'
                ];
            });

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Prerequisite failed: Accept scoring not permitted'],
            $result->getMessages()
        );
    }

    private function getIrhpApplicationMock(
        mixed $id,
        mixed $licenceId,
        mixed $permitsAwarded,
        mixed $successLevel,
        mixed $outcomeNotificationType,
        mixed $checked
    ): m\MockInterface {
        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getId')
            ->andReturn($licenceId);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($id);
        $irhpApplication->shouldReceive('getLicence')
            ->andReturn($licence);
        $irhpApplication->shouldReceive('getPermitsAwarded')
            ->andReturn($permitsAwarded);
        $irhpApplication->shouldReceive('getSuccessLevel')
            ->andReturn($successLevel);
        $irhpApplication->shouldReceive('getOutcomeNotificationType')
            ->andReturn($outcomeNotificationType);
        $irhpApplication->shouldReceive('getChecked')
            ->andReturn($checked);
        $irhpApplication->shouldReceive('getIssueFeeProductReference')
            ->andReturn($this->issueFeeProductReference);
        $irhpApplication->shouldReceive('getEmailCommandLookup')
            ->andReturn($this->emailCommandLookup);

        return $irhpApplication;
    }
}
