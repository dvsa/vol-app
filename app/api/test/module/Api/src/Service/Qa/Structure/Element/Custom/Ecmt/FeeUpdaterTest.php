<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Fees\EcmtApplicationFeeCommandCreator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\FeeUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FeeUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FeeUpdaterTest extends MockeryTestCase
{
    public function testUpdateFees(): void
    {
        $permitsRequired = 47;
        $outstandingIssueFee1Id = 76;
        $outstandingIssueFee2Id = 78;

        $outstandingIssueFee1 = m::mock(FeeEntity::class);
        $outstandingIssueFee1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($outstandingIssueFee1Id);

        $outstandingIssueFee2 = m::mock(FeeEntity::class);
        $outstandingIssueFee2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($outstandingIssueFee2Id);

        $outstandingIssueFees = [
            $outstandingIssueFee1,
            $outstandingIssueFee2
        ];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getOutstandingApplicationFees')
            ->withNoArgs()
            ->andReturn($outstandingIssueFees);

        $cancelFeeCommand1 = CancelFeeCmd::create([]);
        $cancelFeeCommand2 = CancelFeeCmd::create([]);
        $createFeeCommand = CreateFeeCmd::create([]);

        $commandCreator = m::mock(CommandCreator::class);
        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $outstandingIssueFee1Id])
            ->andReturn($cancelFeeCommand1);
        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $outstandingIssueFee2Id])
            ->andReturn($cancelFeeCommand2);

        $commandHandlerManager = m::mock(CommandHandlerManager::class);
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand1, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand2, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($createFeeCommand, false)
            ->once();

        $ecmtApplicationFeeCommandCreator = m::mock(EcmtApplicationFeeCommandCreator::class);
        $ecmtApplicationFeeCommandCreator->shouldReceive('create')
            ->with($irhpApplication, $permitsRequired)
            ->andReturn($createFeeCommand);

        $feeUpdater = new FeeUpdater(
            $commandCreator,
            $commandHandlerManager,
            $ecmtApplicationFeeCommandCreator
        );

        $feeUpdater->updateFees($irhpApplication, $permitsRequired);
    }
}
