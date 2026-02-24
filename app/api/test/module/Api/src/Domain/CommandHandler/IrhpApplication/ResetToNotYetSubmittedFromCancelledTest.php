<?php

declare(strict_types=1);

/**
 * Reset to Not Yet Submitted from Cancelled test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\ResetToNotYetSubmittedFromCancelled as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\Fees\EcmtApplicationFeeCommandCreator;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromCancelled as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Reset to Not Yet Submitted from Cancelled test
 */
class ResetToNotYetSubmittedFromCancelledTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsFeesEcmtApplicationFeeCommandCreator' => m::mock(ApplicationFeeCommandCreator::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_NOT_YET_SUBMITTED
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $irhpApplicationId = 459;
        $permitsRequired = 13;

        $command = Cmd::create(
            [
                'id' => $irhpApplicationId,
            ]
        );

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getTotalEmissionsCategoryPermitsRequired')
            ->withNoArgs()
            ->andReturn($permitsRequired);
        $irhpApplication->shouldReceive('resetToNotYetSubmittedFromCancelled')
            ->with($this->refData[IrhpInterface::STATUS_NOT_YET_SUBMITTED])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $createFeeParams = [
            'licence' => 122,
            'irhpApplication' => $irhpApplicationId,
            'invoicedDate' => '2020-12-06',
            'description' => 'fee description',
            'feeType' => 822,
            'feeStatus' => Fee::STATUS_OUTSTANDING,
            'quantity' => $permitsRequired
        ];

        $this->expectedSideEffect(
            CreateFee::class,
            $createFeeParams,
            new Result()
        );

        $createFeeCommand = CreateFee::create($createFeeParams);

        $this->mockedSmServices['PermitsFeesEcmtApplicationFeeCommandCreator']->shouldReceive('create')
            ->with($irhpApplication, $permitsRequired)
            ->andReturn($createFeeCommand);

        $this->sut->handleCommand($command);
    }
}
